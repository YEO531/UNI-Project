#include <iostream>
#include <cstdlib>
#include <limits>
#include "timer.h"
using namespace std;

void printArray(int Array[], int n)
{
    for(int i = 0; i < n; i++)
    {
        cout << Array[i] << " ";
    }
    endl(cout);
}

void swap(int &x, int &y)
{
    int temp = x;
    x = y;
    y = temp;
}

void bubble_sort(int Array[], int n, bool ascend_order)
{
    for(int i = 0; i < n; i++)
    {
        for(int j = 0; j < n - i - 1; j++)
        {
            if(ascend_order)
            {
                if(Array[j] > Array[j + 1])
                {
                    swap(Array[j], Array[j + 1]);
                }
            }
            else
            {
                if(Array[j] < Array[j + 1])
                {
                    swap(Array[j], Array[j + 1]);
                }
            }
        }
        cout << "Pass " << i + 1 << ": ";
        printArray(Array, n);
    }
}

int main()
{
    int n;
    bool ascend_order;
    char choice;

    do{
        cout << "Enter the size of the array [10, 100, 1000, 10000]: ";
        cin >> n;

        if (n == 10 || n == 100 || n == 1000 || n == 10000)
        {
            break;
        }
        else
        {
            cout << "Invalid number. Please enter 10, 100, 1000, or 10000!\n";
            cin.clear();
            cin.ignore(numeric_limits<streamsize>::max(),'\n');
        }
    }while(true);

    int Array[n];

    for(int i = 0; i < n; i++)
    {
        Array[i] = rand() % 10001;
    }

    cout << "Contents of array generated:\n";

    printArray(Array, n);


    cout << "Do you want to sort the array in ascending order[y] or descending order[n]: ";
    cin >> choice;

    if(choice == 'Y' || choice == 'y')
    {
        ascend_order = true;
    }
    else
    {
        ascend_order = false;
    }

    TICK();
    bubble_sort(Array, n, ascend_order);
    TOCK();
    cout << "\nExecution time: " << DURATION() << " seconds";


    return 0;
}

