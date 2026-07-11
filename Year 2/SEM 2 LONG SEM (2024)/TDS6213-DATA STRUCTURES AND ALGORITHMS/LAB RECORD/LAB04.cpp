#include <iostream>
using namespace std;

int sum_recursive(int n)
{
    if (n == 0) return 0;
    return n + sum_recursive(n - 1);
}

