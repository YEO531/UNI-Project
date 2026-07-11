"""
Part 2 clustering experiment runner for TDA6323.

The three selected algorithms are separated into their own files:
- kmeans_algorithm.py
- dbscan_algorithm.py
- hierarchical_algorithm.py

Reference repositories used for algorithm selection and implementation guidance:
- https://github.com/Akshay-Gulhane15/Hierarchical-Clustering-DBSCAN-KMeans
- https://github.com/sandipanpaul21/Clustering-in-Python

Run this file to reproduce the timing experiment, CSV results, and figures.
"""

from __future__ import annotations

import argparse
import csv
import math
import time
from dataclasses import dataclass
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageFont

from cuda_backend import get_backend
from dbscan_algorithm import dbscan
from hierarchical_algorithm import hierarchical_agglomerative
from kmeans_algorithm import kmeans


ALGORITHMS = ("K-Means", "DBSCAN", "Hierarchical")
DEFAULT_SIZES = (200, 400, 800, 1200, 1600)


@dataclass(frozen=True)
class TimingResult:
    algorithm: str
    input_size: int
    elapsed_ms: float
    clusters_found: int
    noise_points: int


@dataclass(frozen=True)
class AlgorithmProfile:
    objective: str
    parameters: str
    complexity: str
    design_steps: tuple[str, ...]
    expected_behavior: str


ALGORITHM_PROFILES = {
    "K-Means": AlgorithmProfile(
        objective="Separate the unlabelled data into four compact centroid-based groups.",
        parameters="k = 4, max_iter = 100, tolerance = 1e-4.",
        complexity="O(Inkd), where I is iterations, n is records, k is clusters, and d is features.",
        design_steps=(
            "Choose four initial centroids from the data.",
            "Assign every point to the nearest centroid using Euclidean distance.",
            "Recalculate each centroid using the mean of assigned points.",
            "Repeat assignment and update until the centroids stop moving.",
        ),
        expected_behavior=(
            "Usually fastest on this dataset because the clusters are mostly compact and rounded. "
            "It does not mark noise separately, so outliers are forced into the nearest cluster."
        ),
    ),
    "DBSCAN": AlgorithmProfile(
        objective="Find dense regions and mark sparse points as noise.",
        parameters="eps = 0.34, min_samples = 5.",
        complexity="O(n^2 d) in this direct distance-matrix implementation.",
        design_steps=(
            "Calculate each point's epsilon-neighbourhood.",
            "Treat points with at least min_samples neighbours as core points.",
            "Expand a cluster from each core point through density-reachable neighbours.",
            "Label points that cannot be reached from dense regions as noise.",
        ),
        expected_behavior=(
            "Better than K-Means for noise handling because scattered points can be labelled -1. "
            "Its result depends strongly on eps and min_samples, especially when dataset density changes."
        ),
    ),
    "Hierarchical": AlgorithmProfile(
        objective="Build a bottom-up hierarchy of clusters and cut it into four final groups.",
        parameters="k = 4 final clusters, average linkage.",
        complexity="O(n^3) for the straightforward repeated distance-matrix merging design.",
        design_steps=(
            "Start with each data point as its own cluster.",
            "Find the closest two active clusters.",
            "Merge those clusters and update average-linkage distances.",
            "Repeat until four clusters remain.",
        ),
        expected_behavior=(
            "Most expensive on large input because it repeatedly scans and updates cluster distances. "
            "It is useful for explaining nested cluster structure, but less practical for very large data."
        ),
    ),
}


def standardize(data: np.ndarray) -> np.ndarray:
    """Scale each feature to zero mean and unit variance."""
    mean = data.mean(axis=0)
    std = data.std(axis=0)
    std[std == 0] = 1.0
    return (data - mean) / std


def generate_dataset(n_samples: int, seed: int = 42) -> np.ndarray:
    """
    Generate the clustering problem used by all algorithms.

    The generated dataset has four natural two-dimensional groups and a small
    number of scattered points. The task is unsupervised: the algorithms only
    receive coordinates and must discover the group structure themselves.
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
        cluster = rng.normal(loc=center, scale=[0.85, 0.75], size=(count, 2))
        clusters.append(cluster)

    data = np.vstack(clusters)
    noise_count = max(1, n_samples // 20)
    noise = rng.uniform(low=-7.5, high=7.5, size=(noise_count, 2))
    data[:noise_count] = noise
    rng.shuffle(data)
    return standardize(data)


def describe_dataset(data: np.ndarray) -> str:
    x_min, y_min = data.min(axis=0)
    x_max, y_max = data.max(axis=0)
    return (
        f"records={len(data)}, features=2, "
        f"x_range=({x_min:.2f}, {x_max:.2f}), "
        f"y_range=({y_min:.2f}, {y_max:.2f})"
    )


def count_clusters(labels: np.ndarray) -> tuple[int, int]:
    cluster_labels = sorted(int(label) for label in set(labels) if label != -1)
    noise_points = int(np.sum(labels == -1))
    return len(cluster_labels), noise_points


def time_algorithm(name: str, data: np.ndarray, seed: int, device: str) -> TimingResult:
    start = time.perf_counter()
    if name == "K-Means":
        labels = kmeans(data, k=4, seed=seed, device=device)
    elif name == "DBSCAN":
        labels = dbscan(data, eps=0.34, min_samples=5, device=device)
    elif name == "Hierarchical":
        labels = hierarchical_agglomerative(data, k=4, device=device)
    else:
        raise ValueError(f"Unknown algorithm: {name}")
    elapsed_ms = (time.perf_counter() - start) * 1000.0
    clusters_found, noise_points = count_clusters(labels)
    return TimingResult(name, len(data), elapsed_ms, clusters_found, noise_points)


def print_algorithm_section_header(index: int, algorithm: str) -> None:
    profile = ALGORITHM_PROFILES[algorithm]
    print("\n" + "=" * 78)
    print(f"SECTION {index}: {algorithm}")
    print("=" * 78)
    print(f"Solving problem: {profile.objective}")
    print(f"Parameters: {profile.parameters}")
    print(f"Theoretical analysis: {profile.complexity}")
    print("How the algorithm solves the clustering problem:")
    for step_index, step in enumerate(profile.design_steps, start=1):
        print(f"  {step_index}. {step}")
    print(f"Expected behaviour: {profile.expected_behavior}")
    print("\nExperimental results:")


def print_algorithm_summary(algorithm: str, section_results: list[TimingResult]) -> None:
    first = section_results[0]
    last = section_results[-1]
    time_ratio = last.elapsed_ms / first.elapsed_ms if first.elapsed_ms > 0 else 0.0
    size_ratio = last.input_size / first.input_size
    print("\nSection finding:")
    print(
        f"  Input size increased {size_ratio:.1f}x "
        f"({first.input_size} to {last.input_size} records)."
    )
    print(
        f"  Execution time increased about {time_ratio:.1f}x "
        f"({first.elapsed_ms:.3f} ms to {last.elapsed_ms:.3f} ms)."
    )
    if algorithm == "K-Means":
        print("  K-Means stays fast because only centroid distances and centroid means are updated.")
    elif algorithm == "DBSCAN":
        print("  DBSCAN grows more clearly because every point needs neighbourhood checking.")
        print("  Noise counts show how DBSCAN separates sparse points instead of forcing every point into a cluster.")
    else:
        print("  Hierarchical clustering grows the most because many cluster-pair distances are scanned during merging.")


def run_experiment(sizes: tuple[int, ...], repeat: int = 2, device: str = "auto") -> list[TimingResult]:
    results: list[TimingResult] = []
    datasets = {size: generate_dataset(size) for size in sizes}
    backend = get_backend(device)

    print("CLUSTERING SOLVING PROBLEM")
    print("-" * 78)
    print("The same generated dataset pattern is used for all algorithms.")
    print("Goal: discover four natural groups from unlabelled 2D points and compare runtime growth.")
    print(f"Input sizes tested: {', '.join(str(size) for size in sizes)}")
    print(f"Timing repeat per algorithm and size: {repeat}")
    print(f"Device: {backend.name}")
    print(backend.note)
    if backend.using_cuda:
        warmup_data = generate_dataset(32)
        kmeans(warmup_data, k=4, seed=999, device=device)
        dbscan(warmup_data, eps=0.34, min_samples=5, device=device)
        hierarchical_agglomerative(warmup_data, k=4, device=device)
        print("CUDA warm-up completed before timing.")
    print(f"Smallest dataset summary: {describe_dataset(datasets[sizes[0]])}")
    print(f"Largest dataset summary:  {describe_dataset(datasets[sizes[-1]])}")

    for section_index, algorithm in enumerate(ALGORITHMS, start=1):
        print_algorithm_section_header(section_index, algorithm)
        section_results: list[TimingResult] = []
        for size in sizes:
            data = datasets[size]
            measurements = [time_algorithm(algorithm, data, seed=42 + i, device=device) for i in range(repeat)]
            median = sorted(measurements, key=lambda item: item.elapsed_ms)[len(measurements) // 2]
            results.append(median)
            section_results.append(median)
            print(
                f"  n={size:4d} "
                f"time={median.elapsed_ms:10.3f} ms "
                f"clusters={median.clusters_found} noise={median.noise_points}"
            )
        print_algorithm_summary(algorithm, section_results)
    return results


def write_csv(results: list[TimingResult], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    with output_path.open("w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["algorithm", "input_size", "elapsed_ms", "clusters_found", "noise_points"])
        for row in results:
            writer.writerow(
                [
                    row.algorithm,
                    row.input_size,
                    f"{row.elapsed_ms:.6f}",
                    row.clusters_found,
                    row.noise_points,
                ]
            )


def write_detailed_analysis(results: list[TimingResult], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    lines = [
        "TDA6323 Part 2 Detailed Clustering Analysis",
        "",
        "Problem:",
        "The experiment generates larger unlabelled 2D datasets with four natural groups and scattered points.",
        "Each algorithm solves the same clustering problem using a different design approach.",
        "",
    ]
    for index, algorithm in enumerate(ALGORITHMS, start=1):
        profile = ALGORITHM_PROFILES[algorithm]
        section_results = [row for row in results if row.algorithm == algorithm]
        first = section_results[0]
        last = section_results[-1]
        time_ratio = last.elapsed_ms / first.elapsed_ms if first.elapsed_ms > 0 else 0.0
        size_ratio = last.input_size / first.input_size
        lines.extend(
            [
                f"Section {index}: {algorithm}",
                f"Solving problem: {profile.objective}",
                f"Parameters: {profile.parameters}",
                f"Theoretical analysis: {profile.complexity}",
                "Design steps:",
            ]
        )
        for step_index, step in enumerate(profile.design_steps, start=1):
            lines.append(f"{step_index}. {step}")
        lines.append("Experiment results:")
        for row in section_results:
            lines.append(
                f"- n={row.input_size}, time={row.elapsed_ms:.3f} ms, "
                f"clusters={row.clusters_found}, noise={row.noise_points}"
            )
        lines.extend(
            [
                "Finding:",
                f"Input size increased {size_ratio:.1f}x and execution time increased about {time_ratio:.1f}x.",
                profile.expected_behavior,
                "",
            ]
        )
    output_path.write_text("\n".join(lines), encoding="utf-8")


def load_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        "C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibrib.ttf" if bold else "C:/Windows/Fonts/calibri.ttf",
    ]
    for candidate in candidates:
        try:
            return ImageFont.truetype(candidate, size=size)
        except OSError:
            continue
    return ImageFont.load_default()


def draw_timing_graph(results: list[TimingResult], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    width, height = 1200, 760
    margin_left, margin_right = 105, 55
    margin_top, margin_bottom = 95, 105
    plot_width = width - margin_left - margin_right
    plot_height = height - margin_top - margin_bottom

    image = Image.new("RGB", (width, height), "white")
    draw = ImageDraw.Draw(image)
    title_font = load_font(30, bold=True)
    label_font = load_font(20, bold=True)
    tick_font = load_font(17)
    legend_font = load_font(18)

    colors = {
        "K-Means": (33, 115, 185),
        "DBSCAN": (219, 103, 35),
        "Hierarchical": (42, 146, 94),
    }
    sizes = sorted({row.input_size for row in results})
    max_time = max(row.elapsed_ms for row in results)
    y_max = max(1.0, math.ceil(max_time * 1.15 / 100.0) * 100.0)

    def x_pos(size: int) -> int:
        if len(sizes) == 1:
            return margin_left + plot_width // 2
        ratio = (size - min(sizes)) / (max(sizes) - min(sizes))
        return int(margin_left + ratio * plot_width)

    def y_pos(ms: float) -> int:
        return int(margin_top + plot_height - (ms / y_max) * plot_height)

    draw.text((width // 2, 34), "Execution Time vs Input Size", fill=(30, 30, 30), font=title_font, anchor="mm")

    tick_count = 5
    for tick in range(tick_count + 1):
        value = y_max * tick / tick_count
        y = y_pos(value)
        draw.line((margin_left, y, width - margin_right, y), fill=(228, 232, 238), width=1)
        draw.text((margin_left - 14, y), f"{value:.0f}", fill=(65, 65, 65), font=tick_font, anchor="rm")

    draw.line((margin_left, margin_top, margin_left, margin_top + plot_height), fill=(25, 25, 25), width=2)
    draw.line(
        (margin_left, margin_top + plot_height, width - margin_right, margin_top + plot_height),
        fill=(25, 25, 25),
        width=2,
    )

    for size in sizes:
        x = x_pos(size)
        draw.line((x, margin_top + plot_height, x, margin_top + plot_height + 8), fill=(25, 25, 25), width=2)
        draw.text((x, margin_top + plot_height + 18), str(size), fill=(45, 45, 45), font=tick_font, anchor="mt")

    draw.text((width // 2, height - 34), "Input size, n", fill=(30, 30, 30), font=label_font, anchor="mm")
    draw.text((margin_left, margin_top - 30), "Execution time (ms)", fill=(30, 30, 30), font=label_font, anchor="ls")

    by_algorithm: dict[str, list[TimingResult]] = {name: [] for name in ALGORITHMS}
    for row in results:
        by_algorithm[row.algorithm].append(row)

    for algorithm in ALGORITHMS:
        points = []
        for row in sorted(by_algorithm[algorithm], key=lambda item: item.input_size):
            points.append((x_pos(row.input_size), y_pos(row.elapsed_ms)))
        if len(points) >= 2:
            draw.line(points, fill=colors[algorithm], width=4)
        for point in points:
            x, y = point
            draw.ellipse((x - 6, y - 6, x + 6, y + 6), fill=colors[algorithm], outline="white", width=2)

    legend_x = margin_left + 18
    legend_y = margin_top + 12
    for idx, algorithm in enumerate(ALGORITHMS):
        y = legend_y + idx * 30
        draw.rectangle((legend_x, y - 8, legend_x + 20, y + 8), fill=colors[algorithm])
        draw.text((legend_x + 30, y), algorithm, fill=(35, 35, 35), font=legend_font, anchor="lm")

    image.save(output_path)


def draw_cluster_preview(output_path: Path, device: str = "auto") -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    data = generate_dataset(1600)
    labels = kmeans(data, k=4, device=device)
    width, height = 900, 620
    margin = 70
    image = Image.new("RGB", (width, height), "white")
    draw = ImageDraw.Draw(image)
    title_font = load_font(26, bold=True)
    label_font = load_font(16)
    colors = [
        (33, 115, 185),
        (219, 103, 35),
        (42, 146, 94),
        (147, 76, 170),
        (80, 80, 80),
    ]
    x_min, y_min = data.min(axis=0) - 0.25
    x_max, y_max = data.max(axis=0) + 0.25

    def px(point: np.ndarray) -> tuple[int, int]:
        x = margin + (point[0] - x_min) / (x_max - x_min) * (width - 2 * margin)
        y = height - margin - (point[1] - y_min) / (y_max - y_min) * (height - 2 * margin)
        return int(x), int(y)

    draw.text(
        (width // 2, 34),
        "Large Generated Dataset Clustered by K-Means",
        fill=(30, 30, 30),
        font=title_font,
        anchor="mm",
    )
    draw.rectangle((margin, margin, width - margin, height - margin), outline=(30, 30, 30), width=2)
    for point, label in zip(data, labels):
        x, y = px(point)
        color = colors[int(label) % len(colors)]
        draw.ellipse((x - 4, y - 4, x + 4, y + 4), fill=color, outline="white")
    draw.text((width // 2, height - 22), "Standardized feature 1", fill=(30, 30, 30), font=label_font, anchor="mm")
    draw.text((margin + 10, margin + 10), "Feature 2", fill=(30, 30, 30), font=label_font, anchor="la")
    image.save(output_path)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run clustering timing experiment.")
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=Path(__file__).resolve().parents[1] / "results",
        help="Directory for CSV and graph outputs.",
    )
    parser.add_argument(
        "--sizes",
        type=int,
        nargs="+",
        default=list(DEFAULT_SIZES),
        help="Input sizes to benchmark.",
    )
    parser.add_argument("--repeat", type=int, default=2, help="Number of runs per algorithm and size.")
    parser.add_argument(
        "--device",
        choices=["auto", "cuda", "cpu"],
        default="auto",
        help="Use CUDA with CuPy when available, force CUDA, or force CPU.",
    )
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    sizes = tuple(int(size) for size in args.sizes)
    results = run_experiment(sizes=sizes, repeat=args.repeat, device=args.device)

    csv_path = args.output_dir / "execution_times.csv"
    analysis_path = args.output_dir / "detailed_algorithm_analysis.txt"
    graph_path = args.output_dir / "execution_time_graph.png"
    preview_path = args.output_dir / "cluster_preview.png"
    write_csv(results, csv_path)
    write_detailed_analysis(results, analysis_path)
    draw_timing_graph(results, graph_path)
    draw_cluster_preview(preview_path, device=args.device)

    print(f"\nSaved timing data: {csv_path}")
    print(f"Saved detailed analysis: {analysis_path}")
    print(f"Saved timing graph: {graph_path}")
    print(f"Saved cluster preview: {preview_path}")


if __name__ == "__main__":
    main()
