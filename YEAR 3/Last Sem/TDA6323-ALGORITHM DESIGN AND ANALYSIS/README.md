# TDA6323 Part 2 Package

Topic: Clustering  
Lab Section: 2A  
Group No: 4

## Contents

- `reports/Part2_Report_Section.pdf` - Part 2 report section containing theoretical analysis, experimental analysis, graph, findings, improvement idea, conclusion, references, and algorithm code appendix.
- `reports/2A_Group4_Final_Report_Draft.pdf` - existing Part 1 PDF appended with the new Part 2 section.
- `2A_Group4_clustering_project.ipynb` - Jupyter notebook version of the clustering experiment.
- `Algorithm Python Code/kmeans_algorithm.py` - K-Means implementation with its own standalone experiment.
- `Algorithm Python Code/dbscan_algorithm.py` - DBSCAN implementation with its own standalone experiment.
- `Algorithm Python Code/hierarchical_algorithm.py` - agglomerative hierarchical clustering implementation with its own standalone experiment.
- `Algorithm Python Code/2A_Group4_clustering_algorithms.py` - main runner for timing experiments, CSV output, and graph generation.
- `results/execution_times.csv` - recorded timing results.
- `results/detailed_algorithm_analysis.txt` - section-by-section explanation of how each algorithm solves the larger clustering problem.
- `results/execution_time_graph.png` - timing graph for the report.
- `results/cluster_preview.png` - sample generated clustering dataset image.

## How to Run

Use Python with NumPy and Pillow installed:

```powershell
python "Algorithm Python Code/2A_Group4_clustering_algorithms.py"
```

The program will regenerate:

- `results/execution_times.csv`
- `results/detailed_algorithm_analysis.txt`
- `results/execution_time_graph.png`
- `results/cluster_preview.png`

The main runner uses larger default input sizes: 200, 400, 800, 1200, and 1600 records.

You can also open and run the notebook:

```powershell
Part2/2A_Group4_clustering_project.ipynb
```

Each algorithm file can also be run by itself:

```powershell
python "Algorithm Python Code/kmeans_algorithm.py"
python "Algorithm Python Code/dbscan_algorithm.py"
python "Algorithm Python Code/hierarchical_algorithm.py"
```

These commands regenerate:

- `results/kmeans_execution_times.csv`
- `results/dbscan_execution_times.csv`
- `results/hierarchical_execution_times.csv`

The report-builder utility is kept outside the program folder at `tools/build_part2_report.py`. It is not part of the algorithm submission file. To rebuild the PDF report:

```powershell
python tools/build_part2_report.py
```

## Code Citation

The implementation topic and algorithm selection are based on the GitHub repositories provided by the group:

- https://github.com/Akshay-Gulhane15/Hierarchical-Clustering-DBSCAN-KMeans
- https://github.com/sandipanpaul21/Clustering-in-Python

The submitted Python files are original NumPy implementations so that all algorithms can be tested under the same experimental setup.
