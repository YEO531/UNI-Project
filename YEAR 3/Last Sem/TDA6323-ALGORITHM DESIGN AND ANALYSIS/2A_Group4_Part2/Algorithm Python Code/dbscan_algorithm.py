"""
DBSCAN clustering implementation for the TDA6323 Part 2 experiment.

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


def standardize(data: np.ndarray) -> np.ndarray:
    """Scale each feature to zero mean and unit variance."""
    mean = data.mean(axis=0)
    std = data.std(axis=0)
    std[std == 0] = 1.0
    return (data - mean) / std


def generate_clustering_problem(n_samples: int, seed: int = 42) -> np.ndarray:
    """
    Create the clustering problem for the experiment.

    The problem is to group unlabelled 2D points by density and mark sparse
    scattered points as noise.
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


def dbscan(data: np.ndarray, eps: float = 0.34, min_samples: int = 5, device: str = "auto") -> np.ndarray:
    """
    Density-based clustering.

    Label -1 is noise. The implementation uses a full pairwise distance matrix,
    giving O(n^2) time and O(n^2) memory for transparent analysis.
    """
    backend = get_backend(device)
    xp = backend.xp

    if backend.kind == "torch":
        data_gpu = xp.as_tensor(data, dtype=xp.float64, device=backend.torch_device)
        n_samples = len(data_gpu)
        distances = xp.linalg.norm(data_gpu[:, None, :] - data_gpu[None, :, :], dim=2)
        neighbor_matrix = to_cpu(distances <= eps)
        synchronize(xp)
        neighborhoods = [np.flatnonzero(neighbor_matrix[i]) for i in range(n_samples)]
    else:
        data_gpu = xp.asarray(data, dtype=xp.float64)
        n_samples = len(data_gpu)
        distances = xp.linalg.norm(data_gpu[:, None, :] - data_gpu[None, :, :], axis=2)
        neighbor_matrix = to_cpu(distances <= eps)
        synchronize(xp)
        neighborhoods = [np.flatnonzero(neighbor_matrix[i]) for i in range(n_samples)]

    labels = np.full(n_samples, fill_value=-99, dtype=int)
    cluster_id = 0

    for point_idx in range(n_samples):
        if labels[point_idx] != -99:
            continue

        neighbors = neighborhoods[point_idx]
        if len(neighbors) < min_samples:
            labels[point_idx] = -1
            continue

        labels[point_idx] = cluster_id
        search_queue = list(int(i) for i in neighbors if i != point_idx)
        queue_pos = 0

        while queue_pos < len(search_queue):
            neighbor_idx = search_queue[queue_pos]
            queue_pos += 1

            if labels[neighbor_idx] == -1:
                labels[neighbor_idx] = cluster_id
            if labels[neighbor_idx] != -99:
                continue

            labels[neighbor_idx] = cluster_id
            neighbor_neighbors = neighborhoods[neighbor_idx]
            if len(neighbor_neighbors) >= min_samples:
                for candidate in neighbor_neighbors:
                    candidate = int(candidate)
                    if labels[candidate] in (-99, -1):
                        search_queue.append(candidate)

        cluster_id += 1

    labels[labels == -99] = -1
    return labels


def count_clusters_and_noise(labels: np.ndarray) -> tuple[int, int]:
    clusters_found = len(set(int(label) for label in labels if label != -1))
    noise_points = int(np.sum(labels == -1))
    return clusters_found, noise_points


def run_experiment(
    sizes: tuple[int, ...] = DEFAULT_SIZES,
    repeat: int = 3,
    output_path: Path | None = None,
    device: str = "auto",
) -> list[tuple[int, float, int, int]]:
    """Run DBSCAN on different input sizes and save timing results."""
    if output_path is None:
        output_path = Path(__file__).resolve().parents[1] / "results" / "dbscan_execution_times.csv"

    backend = get_backend(device)
    results: list[tuple[int, float, int, int]] = []
    print("Problem: cluster generated 2D data by density using DBSCAN.")
    print(f"Device: {backend.name}")
    print(backend.note)
    if backend.using_cuda:
        warmup_data = generate_clustering_problem(32)
        dbscan(warmup_data, eps=0.34, min_samples=5, device=device)
        print("CUDA warm-up completed before timing.")
    for size in sizes:
        data = generate_clustering_problem(size)
        elapsed_values = []
        labels = np.array([], dtype=int)
        for _ in range(repeat):
            start = time.perf_counter()
            labels = dbscan(data, eps=0.34, min_samples=5, device=device)
            elapsed_values.append((time.perf_counter() - start) * 1000.0)
        elapsed_ms = sorted(elapsed_values)[len(elapsed_values) // 2]
        clusters_found, noise_points = count_clusters_and_noise(labels)
        results.append((size, elapsed_ms, clusters_found, noise_points))
        print(
            f"n={size:4d} time={elapsed_ms:10.3f} ms "
            f"clusters={clusters_found} noise={noise_points}"
        )

    output_path.parent.mkdir(parents=True, exist_ok=True)
    with output_path.open("w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["algorithm", "input_size", "elapsed_ms", "clusters_found", "noise_points"])
        for size, elapsed_ms, clusters_found, noise_points in results:
            writer.writerow(["DBSCAN", size, f"{elapsed_ms:.6f}", clusters_found, noise_points])
    print(f"Saved DBSCAN experiment results: {output_path}")
    return results


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run the DBSCAN CUDA/CPU clustering experiment.")
    parser.add_argument("--device", choices=["auto", "cuda", "cpu"], default="auto")
    parser.add_argument("--sizes", type=int, nargs="+", default=list(DEFAULT_SIZES))
    parser.add_argument("--repeat", type=int, default=3)
    return parser.parse_args()


if __name__ == "__main__":
    args = parse_args()
    run_experiment(sizes=tuple(args.sizes), repeat=args.repeat, device=args.device)
