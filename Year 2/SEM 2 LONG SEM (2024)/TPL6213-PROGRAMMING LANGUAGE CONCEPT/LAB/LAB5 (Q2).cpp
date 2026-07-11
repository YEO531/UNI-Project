#include <iostream>
#include <cmath>
using namespace std;

int abs = 5;
//error: 'int abs' redeclared as different kind of symbol

corrct way is int std::abs = 5;
