#ifndef TIMER_H
#define TIMER_H

#include <chrono>

static std::chrono::time_point<std::chrono::high_resolution_clock> start_time;
static std::chrono::time_point<std::chrono::high_resolution_clock> end_time;

#define TICK() start_time = std::chrono::high_resolution_clock::now()
#define TOCK() end_time = std::chrono::high_resolution_clock::now()
#define DURATION() std::chrono::duration<double>(end_time - start_time).count()

#endif
