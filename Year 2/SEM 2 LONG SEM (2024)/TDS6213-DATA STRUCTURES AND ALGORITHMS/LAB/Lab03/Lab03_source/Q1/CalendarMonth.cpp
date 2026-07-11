#ifndef CALENDAR_MONTH_CPP
#define CALENDAR_MONTH_CPP

#include <iostream>
#include <string>
using namespace std;

class CalendarMonth
{
    public:
        CalendarMonth(int year, int month);
        ~CalendarMonth();

        void display(int firstDayOfMonth);
        void putEntry(string text, int day);

        string getEntry(int day);
        int getMonth();
        int getYear();
        int getNumberOfDays();

    private:
        int month;
        int year;
        int numOfDays;
        string* entries;
};

CalendarMonth::CalendarMonth(int year, int month): year(year),month(month)
{
    // TODO: Write your code here
    // Begin Question 1(a)
    // (i) Set the year and month


    // use constructor initializer list

    // End Question 1(a)



	// TODO: Write your code here
	 if (month < 1 || month > 12) {
        throw invalid_argument("Invalid month. Must be between 1 and 12.");
    }
    static const int daysInMonth[] = { 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 };
    numOfDays = daysInMonth[month - 1];
    if (month == 2) { // Check leap year
        if ((year % 4 == 0 && year % 100 != 0) || (year % 400 == 0)) {
            numOfDays = 29;
        }
    }
    entries = new string[numOfDays]; // Allocate memory
    for (int i = 0; i < numOfDays; ++i) {
        entries[i] = ""; // Initialize with empty strings
    }
	// Begin Question 1(b)
    // (i) Determine the number of days in the given month year
    // (ii) Check for leap year if month is February
    // feb



    // End Question 1(b)

	// TODO: Write your code here
    // Begin Question 1(c)
    // (i) Allocate memory for entries
    // (ii) Initialize each entry with empty string ""


    // End Question 1(c)
}

CalendarMonth::~CalendarMonth()
{
    // TODO: Write your code here

    delete[] entries;
    entries = nullptr; //NULL also can


	// Begin Question 1(d)
    // (i) Delete memory allocated for entries
    // (ii) Make sure entries points to null


    // End Question 1(d)
}

void CalendarMonth::display(int firstDayOfMonth)
{
    static string dayName[] =
    {
        "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"  // all 3 chars
    };

    static string monthName[] =
    {
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    };

    // check firstDay for error.
    if ( ( firstDayOfMonth < 0 ) || ( firstDayOfMonth > 6 ) )
    {
        cout << "First Day invalid!" << endl;
        return;
    }

    // TODO: Write your code here


	// Begin Question 3(a)
    // (i) Display month and year


    // End Question 3(a)

    cout << "--------------------------------------------------------" << endl;

    // TODO: Write your code here
	// Begin Question 3(b)
    // (i) Display Sunday to Saturday.
    // Hint: Use a tab character to separate the names.


    // End Question 3(b)

    cout << "--------------------------------------------------------" << endl;

    int currentDay = 1;     // the current day to display
    int currentEntry = 0;   // the current entry to display

	// TODO: Write your code here
	// Begin Question 3(c)
    // (i) Display the first week
    // Hint: Display a tab character for days before the firstDayOfMonth
    //       Display the day number for days starting from firstDayOfMonth
    for (int i = 0; i < firstDayOfMonth; i++)
        cout << "\t";

    for (int i = firstDayOfMonth; i < 7; i++)
    {
        // if there is an entry on the current day






    }
    endl(cout);


    // End Question 3(c)

    // TODO: Write your code here
	// Begin Question 3(d)
    // (i) Display the remaining weeks.
    while ( currentDay <= numOfDays )
    {
        // for each week
        for (int i = 0; i < 7; i++)
        {
            if (currentDay <= numOfDays)
            {







            }
        }
        endl(cout);
    }

    // End Question 3(d)

    cout << "--------------------------------------------------------" << endl;

    // TODO: Write your code here
	// Begin Question 3(e)
    // (i) Display a list of entries
    currentEntry = 0;
    for (int i = 0; i < numOfDays; ++i)
    {





    }
    endl(cout);
    // End Question 3(e)
}

void CalendarMonth::putEntry(string text, int day)
{
    // TODO: Write your code here
     if(day >= 1 && day <= numOfDays)
     entries[day - 1] =
    // Begin Question 2(a)
    // (i) Make sure the given day is valid

    // (ii) Assign the appropriate entry with text


    // End Question 2(a)
}

string CalendarMonth::getEntry(int day)
{
    // TODO: Write your code here
    // Begin Question 2(b)
    // (i) Make sure the given day is valid.
    // (ii) Return the entry at the given day.


    // End Question 2(b)
}

int CalendarMonth::getMonth()
{
    return month;
}

int CalendarMonth::getYear()
{
    return year;
}

int CalendarMonth::getNumberOfDays()
{
    return numOfDays;
}
#endif
