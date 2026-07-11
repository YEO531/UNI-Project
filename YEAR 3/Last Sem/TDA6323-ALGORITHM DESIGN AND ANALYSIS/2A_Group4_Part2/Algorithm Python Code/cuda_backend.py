"""
CUDA backend helper for the TDA6323 clustering programs.

The programs use CuPy when it is installed and a CUDA GPU is available.
If CuPy is missing, the code falls back to NumPy so the files still run.
"""

from __future__ import annotations

from dataclasses import dataclass

import numpy as np

try:
    import torch
except Exception:
    torch = None

try:
    import cupy as cp
except Exception:
    cp = None


@dataclass(frozen=True)
class Backend:
    xp: object
    name: str
    using_cuda: bool
    note: str
    kind: str
    torch_device: str | None = None


def get_backend(device: str = "auto") -> Backend:
    """Return the array backend requested by the user."""
    device = device.lower()
    if device not in {"auto", "cuda", "cpu"}:
        raise ValueError("device must be 'auto', 'cuda', or 'cpu'")

    if device == "cpu":
        return Backend(np, "CPU NumPy", False, "CUDA disabled by --device cpu.", "numpy")

    if torch is not None:
        try:
            if torch.cuda.is_available():
                gpu_name = torch.cuda.get_device_name(0)
                return Backend(
                    torch,
                    f"CUDA PyTorch ({gpu_name})",
                    True,
                    "Running GPU tensor operations with PyTorch CUDA.",
                    "torch",
                    "cuda",
                )
        except Exception as exc:
            if device == "cuda":
                return Backend(np, "CPU NumPy", False, f"PyTorch CUDA check failed: {exc}", "numpy")

    if cp is not None:
        try:
            device_count = cp.cuda.runtime.getDeviceCount()
            if device_count > 0:
                gpu_id = cp.cuda.runtime.getDevice()
                props = cp.cuda.runtime.getDeviceProperties(gpu_id)
                gpu_name = props["name"].decode("utf-8")
                # Smoke-test one kernel so bad CuPy/driver combinations fall back cleanly.
                test = cp.asarray([1.0, 2.0, 3.0])
                float(cp.sum(test).get())
                cp.cuda.Stream.null.synchronize()
                return Backend(cp, f"CUDA CuPy ({gpu_name})", True, "Running GPU array operations with CuPy.", "cupy")
        except Exception as exc:
            if device == "cuda":
                return Backend(
                    np,
                    "CPU NumPy",
                    False,
                    f"CUDA requested, but CuPy failed its kernel test and PyTorch CUDA was unavailable: {exc}",
                    "numpy",
                )

    note = "No working CUDA Python backend was found, so the program is using CPU NumPy."
    if device == "cuda":
        note = "CUDA was requested, but no working CUDA Python backend was found. Using CPU NumPy instead."
    return Backend(np, "CPU NumPy", False, note, "numpy")


def to_cpu(array):
    """Convert a NumPy/CuPy array to a NumPy array."""
    if torch is not None and isinstance(array, torch.Tensor):
        return array.detach().cpu().numpy()
    if cp is not None and isinstance(array, cp.ndarray):
        return cp.asnumpy(array)
    return np.asarray(array)


def synchronize(xp: object) -> None:
    """Synchronize CUDA work before timing ends."""
    if torch is not None and xp is torch and torch.cuda.is_available():
        torch.cuda.synchronize()
    if cp is not None and xp is cp:
        cp.cuda.Stream.null.synchronize()
