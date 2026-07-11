#include <iostream>
#include <limits>
#include <cstdlib>
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

void merge(int Array[], int n, int first, int mid, int last, bool ascend_order)
{
    int tempArray[n];
    int left = first;
    int right = mid + 1;
    int index = first;

    while (left <= mid && right <= last)
    {
        if(ascend_order)
        {
            if(Array[left] <= Array[right])
            {
                tempArray[index] = Array[left];
                left++;
            }
            else
            {
                tempArray[index] = Array[right];
                right++;
            }
        }
        else
        {
            if(Array[left] >= Array[right])
            {
                tempArray[index] = Array[left];
                left++;
            }
            else
            {
                tempArray[index] = Array[right];
                right++;
            }
        }
        index++;
    }
    if(left > mid)
    {
        for(int i = right; i <= last; i++)
        {
            tempArray[index] = Array[i];
            index++;
        }
    }
    else if(right > last)
    {
        for (int i = left; i <= mid; i++)
        {
            tempArray[index] = Array[i];
            index++;
        }
    }
    for (int i = first; i <= last; i++)
    {
        Array[i] = tempArray[i];
    }
}

void mergeSort(int Array[], int first, int last, int n, bool ascend_order)
{
    if (first >= last)
    {
        return;
    }

    int mid = (first + last) / 2;
    mergeSort(Array, first, mid, n, ascend_order);
    mergeSort(Array, mid + 1, last, n, ascend_order);
    merge(Array, n, first, mid, last, ascend_order);
    cout << "Pass: ";
    printArray(Array, n);
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
    mergeSort(Array, 0, n - 1, n, ascend_order);
    TOCK();

    cout << "\nExecution time: " << DURATION() << " seconds";



    return 0;
}

