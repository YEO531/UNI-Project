#include <iostream>
#include <cstdlib>   //enable rand() function
#include <limits>    //For numeric limits
#include "timer.h"
using namespace std;

// Function to display the elements of the array
void printArray(int Array[], int n)
{
    for(int i = 0; i < n; i++)
    {
        cout << Array[i] << " ";
    }
    endl(cout);
}

//Function to swap two integers
void swap(int &x, int &y)
{
    int temp = x;
    x = y;
    y = temp;
}

//Bubble sort implementation that includes ascending order and descending order
void bubble_sort(int Array[], int n, bool ascend_order)
{
    bool swapped;
    for(int i = 0; i < n - 1; i++)
    {
        swapped = false;     //Check if swapping happens
        for(int j = 0; j < n - i - 1; j++)
        {
            if(ascend_order)     //Sorting in ascending order
            {
                if(Array[j] > Array[j + 1])
                {
                    swap(Array[j], Array[j + 1]);
                    swapped = true;
                }
            }
            else                ////Sorting in descending order
            {
                if(Array[j] < Array[j + 1])
                {
                    swap(Array[j], Array[j + 1]);
                    swapped = true;
                }
            }
        }
        //If there's no swapping, then array is already sorted
        if(!swapped)
        {
            break;    //exit the loop
        }
        cout << "Pass " << i + 1 << ": ";      //Print the array after each pass
        printArray(Array, n);
    }
}

int main()
{
    int n;              //size of array
    bool ascend_order;  //Check for sorting order
    char choice;        //User input for sorting order
    srand(time(0));      //To generate different sequence of numbers each time the program runs

    //Input validation
    do{
        cout << "Enter the size of the array [10, 100, 500, 2000]: ";
        cin >> n;

        if (n == 10 || n == 100 || n == 500 || n == 2000)
        {
            break;
        }
        else
        {
            cout << "Invalid number. Please enter 10, 100, 500, or 2000!\n";
            cin.clear();                                                     // Clear error flags
            cin.ignore(numeric_limits<streamsize>::max(),'\n');              // Clear input buffer
        }
    }while(true);

    //Declare and initialize the array with random numbers according to the input size
    int Array[n];

    for(int i = 0; i < n; i++)
    {
        Array[i] = rand() % 2001;      //random numbers between 0 and 2000
    }

    //Display original array
    cout << "Original Array:\n";
    printArray(Array, n);

    //User input for ascending or descending order
    cout << "Do you want to sort the array in ascending order[y] or descending order[n or any key]: ";
    cin >> choice;

    if(choice == 'Y' || choice == 'y')
    {
        ascend_order = true;
        cout << "Sort the array in ascending order\n\n";
    }
    else
    {
        ascend_order = false;
        cout << "Sort the array in descending order\n\n";
    }


    TICK();                                 //Start timer
    bubble_sort(Array, n, ascend_order);    //Sort array
    TOCK();                                 //Stop timer
    cout << "\nSorted array: ";
    printArray(Array, n);                   //Display sorted array
    cout << "\nExecution time: " << DURATION() << " seconds";      //Display the execution time


    return 0;
}

