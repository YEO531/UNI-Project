#include <iostream>
using namespace std;

int main() {
    // Compile Time: The type of 'a' and 'b' is bound to 'int'.
    int a = 10; // a is bound to type 'int'.

    // Load Time: Static variables are bound to memory locations.
    static int b = 20; // b is bound to a memory address when the program loads.

    // Runtime: Dynamic memory allocation binds memory to 'ptr' during execution.
    int *ptr = new int(30); // ptr is bound to a memory location at runtime.

    cout << "a: " << a << ", b: " << b << ", *ptr: " << *ptr << endl;

    delete ptr; // Freeing the memory at runtime.
    return 0;
}

