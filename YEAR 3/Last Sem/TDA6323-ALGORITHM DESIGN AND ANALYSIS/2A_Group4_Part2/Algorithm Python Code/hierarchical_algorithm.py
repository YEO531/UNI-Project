"""
Agglomerative hierarchical clustering implementation for the TDA6323 Part 2 experiment.

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

    The problem is to group unlabelled 2D points into a hierarchy, then cut the
    hierarchy into four final clusters.
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


def hierarchical_agglomerative(data: np.ndarray, k: int = 4, device: str = "auto") -> np.ndarray:
    """
    Agglomerative hierarchical clustering with average linkage.

    The implementation starts with each point as one cluster and repeatedly
    merges the closest pair until k clusters remain.
    """
    backend = get_backend(device)
    xp = backend.xp

    if backend.kind == "torch":
        data_gpu = xp.as_tensor(data, dtype=xp.float64, device=backend.torch_device)
        n_samples = len(data_gpu)
        distances = xp.linalg.norm(data_gpu[:, None, :] - data_gpu[None, :, :], dim=2)
        diagonal_indices = xp.arange(n_samples, device=backend.torch_device)
        distances[diagonal_indices, diagonal_indices] = float("inf")

        active = xp.ones(n_samples, dtype=xp.bool, device=backend.torch_device)
        sizes = xp.ones(n_samples, dtype=xp.float64, device=backend.torch_device)
        labels = xp.arange(n_samples, dtype=xp.long, device=backend.torch_device)
        active_count = n_samples

        while active_count > k:
            masked = distances.clone()
            masked[~active, :] = float("inf")
            masked[:, ~active] = float("inf")

            merge_flat_index = int(xp.argmin(masked).item())
            merge_a, merge_b = np.unravel_index(merge_flat_index, masked.shape)
            if merge_a > merge_b:
                merge_a, merge_b = merge_b, merge_a

            active_indices = xp.nonzero(active, as_tuple=False).flatten()
            update_indices = active_indices[(active_indices != merge_a) & (active_indices != merge_b)]

            weight_a = sizes[merge_a]
            weight_b = sizes[merge_b]
            new_distances = (
                weight_a * distances[merge_a, update_indices]
                + weight_b * distances[merge_b, update_indices]
            ) / (weight_a + weight_b)

            distances[merge_a, update_indices] = new_distances
            distances[update_indices, merge_a] = new_distances
            distances[merge_b, :] = float("inf")
            distances[:, merge_b] = float("inf")
            distances[merge_a, merge_a] = float("inf")

            labels[labels == merge_b] = merge_a
            sizes[merge_a] += sizes[merge_b]
            active[merge_b] = False
            active_count -= 1

        synchronize(xp)
        labels_cpu = to_cpu(labels).astype(int)
        unique_roots = {root: idx for idx, root in enumerate(sorted(set(labels_cpu)))}
        return np.array([unique_roots[root] for root in labels_cpu], dtype=int)

    data_gpu = xp.asarray(data, dtype=xp.float64)
    n_samples = len(data_gpu)
    distances = xp.linalg.norm(data_gpu[:, None, :] - data_gpu[None, :, :], axis=2)
    diagonal_indices = xp.arange(n_samples)
    distances[diagonal_indices, diagonal_indices] = float("inf")

    active = xp.ones(n_samples, dtype=bool)
    sizes = xp.ones(n_samples, dtype=xp.float64)
    labels = xp.arange(n_samples, dtype=xp.int32)
    active_count = n_samples

    while active_count > k:
        masked = distances.copy()
        masked[~active, :] = float("inf")
        masked[:, ~active] = float("inf")

        merge_flat_index = int(to_cpu(xp.argmin(masked)))
        merge_a, merge_b = np.unravel_index(merge_flat_index, masked.shape)
        if merge_a > merge_b:
            merge_a, merge_b = merge_b, merge_a

        active_indices = xp.flatnonzero(active)
        update_indices = active_indices[(active_indices != merge_a) & (active_indices != merge_b)]

        weight_a = sizes[merge_a]
        weight_b = sizes[merge_b]
        new_distances = (
            weight_a * distances[merge_a, update_indices]
            + weight_b * distances[merge_b, update_indices]
        ) / (weight_a + weight_b)

        distances[merge_a, update_indices] = new_distances
        distances[update_indices, merge_a] = new_distances
        distances[merge_b, :] = float("inf")
        distances[:, merge_b] = float("inf")
        distances[merge_a, merge_a] = float("inf")

        labels[labels == merge_b] = merge_a
        sizes[merge_a] += sizes[merge_b]
        active[merge_b] = False
        active_count -= 1

    synchronize(xp)
    labels_cpu = to_cpu(labels).astype(int)
    unique_roots = {root: idx for idx, root in enumerate(sorted(set(labels_cpu)))}
    return np.array([unique_roots[root] for root in labels_cpu], dtype=int)


def count_clusters(labels: np.ndarray) -> int:
    return len(set(int(label) for label in labels))


def run_experiment(
    sizes: tuple[int, ...] = DEFAULT_SIZES,
    repeat: int = 3,
    output_path: Path | None = None,
    device: str = "auto",
) -> list[tuple[int, float, int]]:
    """Run agglomerative hierarchical clustering on different input sizes."""
    if output_path is None:
        output_path = Path(__file__).resolve().parents[1] / "results" / "hierarchical_execution_times.csv"

    backend = get_backend(device)
    results: list[tuple[int, float, int]] = []
    print("Problem: cluster generated 2D data using agglomerative hierarchical clustering.")
    print(f"Device: {backend.name}")
    print(backend.note)
    if backend.using_cuda:
        warmup_data = generate_clustering_problem(32)
        hierarchical_agglomerative(warmup_data, k=DEFAULT_CLUSTER_COUNT, device=device)
        print("CUDA warm-up completed before timing.")
    for size in sizes:
        data = generate_clustering_problem(size)
        elapsed_values = []
        labels = np.array([], dtype=int)
        for _ in range(repeat):
            start = time.perf_counter()
            labels = hierarchical_agglomerative(data, k=DEFAULT_CLUSTER_COUNT, device=device)
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
            writer.writerow(["Hierarchical", size, f"{elapsed_ms:.6f}", clusters_found])
    print(f"Saved hierarchical experiment results: {output_path}")
    return results


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run the hierarchical CUDA/CPU clustering experiment.")
    parser.add_argument("--device", choices=["auto", "cuda", "cpu"], default="auto")
    parser.add_argument("--sizes", type=int, nargs="+", default=list(DEFAULT_SIZES))
    parser.add_argument("--repeat", type=int, default=3)
    return parser.parse_args()


if __name__ == "__main__":
    args = parse_args()
    run_experiment(sizes=tuple(args.sizes), repeat=args.repeat, device=args.device)
