"""
K-Means clustering implementation for the TDA6323 Part 2 experiment.

Reference repositories used for algorithm selection and implementation guidance:
- https://github.com/Akshay-Gulhane15/Hierarchical-Clustering-DBSCAN-KMeans
- https://github.com/sandipanpaul21/Clustering-in-Python

This file is an original NumPy implementation written for the experiment.
"""

from __future__ import annotations

import argparse
import csv
import time
from pathlib import Path

import numpy as np

from cuda_backend import get_backend, synchronize, to_cpu


DEFAULT_SIZES = (200, 400, 800, 1200, 1600)
DEFAULT_CLUSTER_COUNT = 4


def standardize(data: np.ndarray) -> np.ndarray:
    """Scale each feature to zero mean and unit variance."""
    mean = data.mean(axis=0)
    std = data.std(axis=0)
    std[std == 0] = 1.0
    return (data - mean) / std


def generate_clustering_problem(n_samples: int, seed: int = 42) -> np.ndarray:
    """
    Create the clustering problem for the experiment.

    The problem is to group unlabelled 2D points into four natural clusters.
    A small number of scattered points is added to make the data less perfect.
    """
    rng = np.random.default_rng(seed + n_samples)
    centers = np.array(
        [
            [-5.0, -4.0],
            [0.0, 0.0],
            [4.5, 4.0],
            [5.0, -4.0],
        ],
        dtype=float,
    )
    counts = np.full(len(centers), n_samples // len(centers), dtype=int)
    counts[: n_samples % len(centers)] += 1

    clusters = []
    for center, count in zip(centers, counts):
        clusters.append(rng.normal(loc=center, scale=[0.85, 0.75], size=(count, 2)))

    data = np.vstack(clusters)
    noise_count = max(1, n_samples // 20)
    data[:noise_count] = rng.uniform(low=-7.5, high=7.5, size=(noise_count, 2))
    rng.shuffle(data)
    return standardize(data)


def kmeans(
    data: np.ndarray,
    k: int = 4,
    max_iter: int = 100,
    tolerance: float = 1e-4,
    seed: int = 42,
    device: str = "auto",
) -> np.ndarray:
    """Cluster data by repeatedly assigning points to their closest centroid."""
    backend = get_backend(device)
    xp = backend.xp

    if backend.kind == "torch":
        data_gpu = xp.as_tensor(data, dtype=xp.float64, device=backend.torch_device)
        rng = np.random.default_rng(seed)
        initial_indices = xp.as_tensor(
            rng.choice(len(data), size=k, replace=False),
            dtype=xp.long,
            device=backend.torch_device,
        )
        centroids = data_gpu[initial_indices].clone()

        labels = xp.zeros(len(data_gpu), dtype=xp.long, device=backend.torch_device)
        for _ in range(max_iter):
            distances = xp.linalg.norm(data_gpu[:, None, :] - centroids[None, :, :], dim=2)
            new_labels = xp.argmin(distances, dim=1)

            new_centroids = centroids.clone()
            for cluster_id in range(k):
                members = data_gpu[new_labels == cluster_id]
                if members.shape[0] > 0:
                    new_centroids[cluster_id] = members.mean(dim=0)

            movement = float(xp.linalg.norm(new_centroids - centroids).item())
            labels = new_labels
            centroids = new_centroids
            if movement <= tolerance:
                break

        synchronize(xp)
        return to_cpu(labels).astype(int)

    data_gpu = xp.asarray(data, dtype=xp.float64)
    rng = np.random.default_rng(seed)
    initial_indices = rng.choice(len(data), size=k, replace=False)
    centroids = data_gpu[initial_indices].copy()

    labels = xp.zeros(len(data_gpu), dtype=xp.int32)
    for _ in range(max_iter):
        distances = xp.linalg.norm(data_gpu[:, None, :] - centroids[None, :, :], axis=2)
        new_labels = xp.argmin(distances, axis=1)

        new_centroids = centroids.copy()
        for cluster_id in range(k):
            members = data_gpu[new_labels == cluster_id]
            if len(members) > 0:
                new_centroids[cluster_id] = members.mean(axis=0)

        movement = float(to_cpu(xp.linalg.norm(new_centroids - centroids)))
        labels = new_labels
        centroids = new_centroids
        if movement <= tolerance:
            break

    synchronize(xp)
    return to_cpu(labels).astype(int)


def count_clusters(labels: np.ndarray) -> int:
    return len(set(int(label) for label in labels))


def run_experiment(
    sizes: tuple[int, ...] = DEFAULT_SIZES,
    repeat: int = 3,
    output_path: Path | None = None,
    device: str = "auto",
) -> list[tuple[int, float, int]]:
    """Run K-Means on different input sizes and save timing results."""
    if output_path is None:
        output_path = Path(__file__).resolve().parents[1] / "results" / "kmeans_execution_times.csv"

    backend = get_backend(device)
    results: list[tuple[int, float, int]] = []
    print("Problem: cluster generated 2D data into four groups using K-Means.")
    print(f"Device: {backend.name}")
    print(backend.note)
    if backend.using_cuda:
        warmup_data = generate_clustering_problem(32)
        kmeans(warmup_data, k=DEFAULT_CLUSTER_COUNT, seed=999, device=device)
        print("CUDA warm-up completed before timing.")
    for size in sizes:
        data = generate_clustering_problem(size)
        elapsed_values = []
        labels = np.array([], dtype=int)
        for run_index in range(repeat):
            start = time.perf_counter()
            labels = kmeans(data, k=DEFAULT_CLUSTER_COUNT, seed=42 + run_index, device=device)
            elapsed_values.append((time.perf_counter() - start) * 1000.0)
        elapsed_ms = sorted(elapsed_values)[len(elapsed_values) // 2]
        clusters_found = count_clusters(labels)
        results.append((size, elapsed_ms, clusters_found))
        print(f"n={size:4d} time={elapsed_ms:10.3f} ms clusters={clusters_found}")

    output_path.parent.mkdir(parents=True, exist_ok=True)
    with output_path.open("w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["algorithm", "input_size", "elapsed_ms", "clusters_found"])
        for size, elapsed_ms, clusters_found in results:
            writer.writerow(["K-Means", size, f"{elapsed_ms:.6f}", clusters_found])
    print(f"Saved K-Means experiment results: {output_path}")
    return results


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run the K-Means CUDA/CPU clustering experiment.")
    parser.add_argument("--device", choices=["auto", "cuda", "cpu"], default="auto")
    parser.add_argument("--sizes", type=int, nargs="+", default=list(DEFAULT_SIZES))
    parser.add_argument("--repeat", type=int, default=3)
    return parser.parse_args()


if __name__ == "__main__":
    args = parse_args()
    run_experiment(sizes=tuple(args.sizes), repeat=args.repeat, device=args.device)
