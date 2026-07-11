#ifndef BORROW_RECORD_H
#define BORROW_RECORD_H

#include <iostream>
#include <iomanip>
#include <ctime>
#include <regex>
#include <stdexcept>
#include <fstream>
#include <sstream>
#include <limits>
#include "BookRecord.h"
using namespace std;

//Hash table implementation of borrowingRecord ADT
class borrowingRecord
{
private:
    struct borrowNode      //Represents a single borrowing record. Contains information about a borrowing process
    {
        int student_id;
        int book_id;
        int borrow_id;
        string student_name;
        string book_name;
        string borrowed_date;
        string return_date;
        float fine_amount;
        bool isOccupied;    // Flag to indicate if this slot is used in hash table

        // Constructor initializes fine amount to 0 and marks slot as unoccupied
        borrowNode()
        {
            fine_amount = 0.0f;
            isOccupied = false;
        }
    };

    static const int TABLE_SIZE = 100;  // Size of the hash table
    borrowNode* hashTable;              // Pointer to the hash table
    bookRecord* bookSystem;             // Pointer to the bookRecord ADT
    int currentSize;                    // Current number of records in the table

    //hash function to calculate hash value (index) based on borrow id (key)
    int hashFunction(int borrow_id)
    {
        return borrow_id % TABLE_SIZE;
    }

    // Linear probing to find next available slot
    int findNextSlot(int index)
    {
        int i = 0;
        while (i < TABLE_SIZE)
        {
            int nextIndex = (index + i) % TABLE_SIZE;
            if (!hashTable[nextIndex].isOccupied)
            {
                return nextIndex;
            }
            i++;
        }
        return -1;  // Table is full
    }

    //Searches for a record with the given borrow ID
    int findRecord(int borrow_id)
    {
        int index = hashFunction(borrow_id);
        int i = 0;
        while (i < TABLE_SIZE)
        {
            int currentIndex = (index + i) % TABLE_SIZE;
            if (!hashTable[currentIndex].isOccupied)
            {
                return -1;  // Record not found
            }
            if (hashTable[currentIndex].isOccupied && hashTable[currentIndex].borrow_id == borrow_id)
            {
                return currentIndex;
            }
            i++;
        }
        return -1;  // Record not found
    }

    //Update the book name in the borrowing records
    void updateBookDetails(int bookId, const string& newBookName)
    {
        for (int i = 0; i < TABLE_SIZE; i++)
        {
            if (hashTable[i].isOccupied && hashTable[i].book_id == bookId)
            {
                hashTable[i].book_name = newBookName;
            }
        }
    }
    //Get the book name from the bookRecord ADT
    string getBookName(int bookId, bool& found)
    {
        string bookName = bookSystem->getBookName(bookId, found);
        if (found)
        {
            // Update borrowing record with this book ID
            updateBookDetails(bookId, bookName);
        }
        return bookName;
    }
    //Function to validate date
    bool isValidDate(const string& date)
    {
        // First check the string format using regex
        regex date_pattern("^\\d{2}/\\d{2}/\\d{4}$");
        if (!regex_match(date, date_pattern))
        {
            return false;
        }

        // Then verify if it's a valid calendar date
        tm tm = {};
        stringstream ss(date);
        ss >> get_time(&tm, "%d/%m/%Y");
        if (ss.fail())
            {
            return false;
        }

        // Convert parsed time back to string to verify values
        char parsed_date[11];
        strftime(parsed_date, sizeof(parsed_date), "%d/%m/%Y", &tm);

        // Compare with original input to catch invalid dates that might parse
        return date == string(parsed_date);
    }
    //User input for date and return formatted date
    string getFormattedDate(const string& prompt)
    {
        string date;
        bool validDate = false;

        while (!validDate)
        {
            cout << prompt;
            getline(cin, date);

            if (isValidDate(date))
            {
                validDate = true;
            }
            else
            {
                cout << "\t\t\tInvalid date format! Please use DD/MM/YYYY format.\n";
            }
        }
        return date;
    }
    //Function to calculate the return due date
    string calculateReturnDate(const string& borrowDate, int borrowPeriod = 7)
    {
        tm tm = {};
        stringstream ss(borrowDate);
        ss >> get_time(&tm, "%d/%m/%Y");

        time_t borrowTime = mktime(&tm);
        time_t returnTime = borrowTime + (borrowPeriod * 24 * 60 * 60);

        struct tm* returnTimePtr;  // Declare first
        returnTimePtr = std::localtime(&returnTime);  // Then assign

        stringstream returnSs;
        returnSs << setfill('0') << setw(2) << returnTimePtr->tm_mday << "/"
            << setfill('0') << setw(2) << (1 + returnTimePtr->tm_mon) << "/"
            << (1900 + returnTimePtr->tm_year);

        return returnSs.str();
    }

    //Function to calculate the days overdue
    int calculateDaysDifference(const string& date1, const string& date2)
    {
        tm tm1 = {}, tm2 = {};
        stringstream ss1(date1), ss2(date2);
        ss1 >> get_time(&tm1, "%d/%m/%Y");
        ss2 >> get_time(&tm2, "%d/%m/%Y");

        time_t time1 = mktime(&tm1);
        time_t time2 = mktime(&tm2);

        return difftime(time2, time1) / (60 * 60 * 24);
    }

    //Function to get the current date
    string getCurrentDate()
    {
        time_t now = time(0);
        tm* ltm = localtime(&now);

        stringstream ss;
        ss << setfill('0') << setw(2) << ltm->tm_mday << "/"
           << setfill('0') << setw(2) << (1 + ltm->tm_mon) << "/"
           << (1900 + ltm->tm_year);

        return ss.str();
    }

    //Partition function for quick sort algorithm based on borrowing records attributes
    int partitionByBorrowId(borrowNode* arr[], int low, int high, bool descending)
    {
        int pivot = arr[high]->borrow_id;
        int i = low - 1;
        for (int j = low; j < high; j++)
        {
            if ((!descending && arr[j]->borrow_id <= pivot) || (descending && arr[j]->borrow_id >= pivot))
            {
                i++;
                borrowNode* temp = arr[i];
                arr[i] = arr[j];
                arr[j] = temp;
            }
        }
        borrowNode* temp = arr[i + 1];
        arr[i + 1] = arr[high];
        arr[high] = temp;
        return i + 1;
    }

    int partitionByBorrowDate(borrowNode* arr[], int low, int high, bool descending)
    {
        string pivot = arr[high]->borrowed_date;
        int i = low - 1;
        for (int j = low; j < high; j++)
        {
            if ((!descending && arr[j]->borrowed_date <= pivot) || (descending && arr[j]->borrowed_date >= pivot))
            {
                i++;
                borrowNode* temp = arr[i];
                arr[i] = arr[j];
                arr[j] = temp;
            }
        }
        borrowNode* temp = arr[i + 1];
        arr[i + 1] = arr[high];
        arr[high] = temp;
        return i + 1;
    }

     int partitionByReturnDate(borrowNode* arr[], int low, int high, bool descending)
     {
        string pivot = arr[high]->return_date;
        int i = low - 1;
        for (int j = low; j < high; j++)
        {
            if ((!descending && arr[j]->return_date <= pivot) || (descending && arr[j]->return_date >= pivot))
            {
                i++;
                borrowNode* temp = arr[i];
                arr[i] = arr[j];
                arr[j] = temp;
            }
        }
        borrowNode* temp = arr[i + 1];
        arr[i + 1] = arr[high];
        arr[high] = temp;
        return i + 1;
    }

    //Quick sort algorithm implementation for sorting based on borrowing records attributes
     void quickSortByBorrowId(borrowNode* arr[], int low, int high, bool descending)
     {
        if (low < high)
        {
            int pi = partitionByBorrowId(arr, low, high, descending);
            quickSortByBorrowId(arr, low, pi - 1, descending);
            quickSortByBorrowId(arr, pi + 1, high, descending);
        }
     }

     void quickSortByBorrowDate(borrowNode* arr[], int low, int high, bool descending)
     {
        if (low < high)
        {
            int pi = partitionByBorrowDate(arr, low, high, descending);
            quickSortByBorrowDate(arr, low, pi - 1, descending);
            quickSortByBorrowDate(arr, pi + 1, high, descending);
        }
     }

    void quickSortByReturnDate(borrowNode* arr[], int low, int high, bool descending)
    {
        if (low < high)
        {
            int pi = partitionByReturnDate(arr, low, high, descending);
            quickSortByReturnDate(arr, low, pi - 1, descending);
            quickSortByReturnDate(arr, pi + 1, high, descending);
        }
    }

public:
    borrowingRecord(bookRecord* books)     //Constructor
    {
        hashTable = new borrowNode[TABLE_SIZE];
        bookSystem = books;
        currentSize = 0;
    }
    ~borrowingRecord()                     //Destructor
    {
        delete[] hashTable;
    }

    //Function to refresh book details (new book name) if there are some changes in bookRecord ADT
    void refreshBookDetails()
    {
        for (int i = 0; i < TABLE_SIZE; i++)
        {
            if (hashTable[i].isOccupied)
            {
                bool found;
                string updatedBookName = bookSystem->getBookName(hashTable[i].book_id, found);
                if (found && updatedBookName != hashTable[i].book_name)
                {
                    hashTable[i].book_name = updatedBookName;
                }
            }
        }
    }

    //Function to add a new book record
    void addRecord()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t ------------------\n";
        cout << "\t\t\t|   Add a Record   |\n";
        cout << "\t\t\t ------------------\n\n";
        cout << "\033[0m";
        if (currentSize >= TABLE_SIZE)
        {
            cout << "\t\t\tError: Record is full!\n";
            return;
        }

        int bookID;
        while (true)
        {
            cout << "\t\t\tEnter Book ID: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    bookID = stoi(input);
                    if (bookID > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Book ID.\n";
        }

        // Check if book exists and is available
        if (!bookSystem->isBookAvailable(bookID))
        {
            cout << "\t\t\tError: Book is either not found or not available for borrowing!\n";
            return;
        }

        bool found;
        string bookName = getBookName(bookID, found);
        if (!found)
        {
            cout << "\t\t\tError: Book not found in the system!\n";
            return;
        }
        updateBookDetails(bookID, bookName);
        cout << "\t\t\tBook Name: " << bookName << endl;

        int borrow_id;
        while (true)
        {
            cout << "\t\t\tEnter Borrow ID: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    borrow_id = stoi(input);
                    if (borrow_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Borrow ID.\n";
        }

        // Check for duplicate borrow ID
        if (findRecord(borrow_id) != -1)
        {
            cout << "\t\t\tError: Borrow ID already exists!\n";
            return;
        }

        //Calculate the index using hash function
        int index = hashFunction(borrow_id);
        //Linear probing
        if (hashTable[index].isOccupied)
        {
            index = findNextSlot(index);
            if (index == -1)
            {
                cout << "\t\t\tError: Hash table is full!\n";
                return;
            }
        }

        hashTable[index].book_id = bookID;
        hashTable[index].borrow_id = borrow_id;
        hashTable[index].book_name = bookName;

        while (true)
        {
            cout << "\t\t\tEnter Student ID: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    hashTable[index].student_id = stoi(input);
                    if (hashTable[index].student_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }

            }
            cout << "\t\t\tError: Please enter a valid positive number for Student ID.\n";
        }

        while (true)
        {
            cout << "\t\t\tEnter Student Name: ";
            getline(cin, hashTable[index].student_name);
            if (hashTable[index].student_name.empty() || hashTable[index].student_name.length() > 50)
            {
                cout << "\t\t\tError: Student name cannot be empty or longer than 50 characters.\n";
                continue;
            }
            // Check if name contains only letters, spaces, and hyphens
            bool validName = true;
            for (char c : hashTable[index].student_name)
            {
                if (!isalpha(c) && c != ' ' && c != '-')
                {
                    validName = false;
                    break;
                }
            }
            if (!validName)
            {
                cout << "\t\t\tError: Name should only contain letters, spaces, and hyphens.\n";
                continue;
            }
            break;
        }

        // Handle borrowed date
        hashTable[index].borrowed_date = getFormattedDate("\t\t\tEnter Borrowed Date (DD/MM/YYYY): ");

        // Automatically calculate return date (7 days from borrow date)
        hashTable[index].return_date = calculateReturnDate(hashTable[index].borrowed_date);
        cout << "\t\t\tReturn Due Date set to: " << hashTable[index].return_date << endl;


        hashTable[index].isOccupied = true;
        currentSize++;

        // Update book status to "Borrowed"
        bookSystem->updateBookStatus(bookID, "Borrowed");
        cout << "\033[33;1m";
        cout << "\t\t\tBorrowing record added successfully!\n";
        cout << "\033[0m";

    }
    //Function to search a borrowing record
    void searchRecord()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t -----------------------\n";
        cout << "\t\t\t|   Search a Record    |\n";
        cout << "\t\t\t -----------------------\n\n";
        cout << "\033[0m";

        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return;
        }

        int borrow_id;
        while (true)
        {
            cout << "\t\t\tEnter Borrow ID to search: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    borrow_id = stoi(input);
                    if (borrow_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Borrow ID.\n";
        }

        int index = findRecord(borrow_id);
        if (index != -1)
        {
            cout << "\033[33;1m";
            cout << "\t\t\tRecord Found!\n\n";
            cout << "\t\t\tBorrow ID: " << hashTable[index].borrow_id << endl;
            cout << "\t\t\tStudent ID: " << hashTable[index].student_id << endl;
            cout << "\t\t\tStudent Name: " << hashTable[index].student_name << endl;
            cout << "\t\t\tBook ID: " << hashTable[index].book_id << endl;
            cout << "\t\t\tBook Name: " << hashTable[index].book_name << endl;
            cout << "\t\t\tBorrowed Date: " << hashTable[index].borrowed_date << endl;
            cout << "\t\t\tReturn Due Date: " << hashTable[index].return_date << endl;
            cout << fixed << setprecision(2);
            cout << "\t\t\tFine Amount: RM " << hashTable[index].fine_amount << endl;
            cout << "\033[0m";
        }
        else
        {
            cout << "\t\t\tBorrowing record not found!\n";
        }
    }

    //Function to edit a borrowing record
    void editRecord()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t ---------------------------\n";
        cout << "\t\t\t|   Edit Borrowing Record   |\n";
        cout << "\t\t\t ---------------------------\n\n";
        cout << "\033[0m";

        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return;
        }

        int borrow_id;
        while (true)
        {
            cout << "\t\t\tEnter Borrow ID to edit: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    borrow_id = stoi(input);
                    if (borrow_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Borrow ID.\n";
        }

        int index = findRecord(borrow_id);

        if (index != -1)
        {
            cout << "\033[33;1m";
            cout << "\t\t\tCurrent Record Details:\n";
            cout << "\t\t\tStudent ID: " << hashTable[index].student_id << endl;
            cout << "\t\t\tStudent Name: " << hashTable[index].student_name << endl;
            cout << "\t\t\tBook ID: " << hashTable[index].book_id << endl;
            cout << "\t\t\tBook Name: " << hashTable[index].book_name << endl;
            cout << "\t\t\tBorrowed Date: " << hashTable[index].borrowed_date << endl;
            cout << "\t\t\tReturn Due Date: " << hashTable[index].return_date << endl;
            cout << "\033[0m";

            cout << "\n\t\t\tEnter new details:\n";

            // Ask if user wants to change the book
            char changeBook;
            bool validChoice = false;
            do
            {
                cout << "\t\t\tDo you want to change the book? (Y/N): ";
                string input;
                getline(cin >> ws, input);

                // Check if input is empty
                if(input.empty())
                {
                    cout << "\t\t\tError: Input cannot be empty. Please enter Y or N.\n";
                    continue;
                }

                // Check if input is exactly one character
                if(input.length() > 1)
                {
                    cout << "\t\t\tError: Please enter a single character (Y or N).\n";
                    continue;
                }

                // Convert to uppercase for comparison
                changeBook = toupper(input[0]);

                if(changeBook == 'Y' || changeBook == 'N')
                {
                    validChoice = true;
                }
                else
                {
                    cout << "\t\t\tError: Invalid input. Please enter Y for Yes or N for No.\n";
                }

            } while(!validChoice);

            // Store old book ID to update status if needed
            int oldBookId = hashTable[index].book_id;

            if (changeBook == 'Y')
            {
                int newBookID;
                while (true)
                {
                    cout << "\t\t\tEnter Book ID: ";
                    string input;
                    getline(cin >> ws, input);

                    // Check if input contains only digits
                    bool validInput = true;
                    for (char c : input)
                    {
                        if (!isdigit(c))
                        {
                            validInput = false;
                            break;
                        }
                    }

                    // Convert to integer if valid
                    if (validInput && !input.empty())
                    {
                        try
                        {
                            newBookID = stoi(input);
                            if (newBookID > 0)
                            {
                                hashTable[index].book_id = newBookID;
                                break;  // Valid input, exit loop
                            }
                        }
                        catch (const std::invalid_argument& e)
                        {
                            // This catches if the string cannot be converted to an integer
                            cout << "\t\t\tError: Invalid input format.\n";
                        }
                        catch (const std::out_of_range& e)
                        {
                            // This catches if the number is too large for an integer
                            cout << "\t\t\tError: Number is too large.\n";
                        }
                    }
                    cout << "\t\t\tError: Please enter a valid positive number for Book ID.\n";
                }


                if (!bookSystem->isBookAvailable(hashTable[index].book_id))
                {
                    cout << "\t\t\tError: New book is either not found or not available!\n";
                    hashTable[index].book_id = oldBookId;  // Restore old book ID
                    return;
                }

                bool found;
                string newBookName = getBookName(hashTable[index].book_id, found);
                if (!found)
                {
                    cout << "\t\t\tError: Book not found in the system!\n";
                    hashTable[index].book_id = oldBookId;  // Restore old book ID
                    return;
                }
                updateBookDetails(hashTable[index].book_id, newBookName);
                cout << "\t\t\tBook Name: " << newBookName << endl;
                hashTable[index].book_name = newBookName;  // Update book name in record
            }

            while (true)
            {
                cout << "\t\t\tEnter Student ID: ";
                string input;
                getline(cin >> ws, input);

                // Check if input contains only digits
                bool validInput = true;
                for (char c : input)
                {
                    if (!isdigit(c))
                    {
                        validInput = false;
                        break;
                    }
                }

                // Convert to integer if valid
                if (validInput && !input.empty())
                {
                    try
                    {
                        hashTable[index].student_id = stoi(input);
                        if (hashTable[index].student_id > 0)
                        {
                            break;  // Valid input, exit loop
                        }
                    }
                    catch (const std::invalid_argument& e)
                    {
                        // This catches if the string cannot be converted to an integer
                        cout << "\t\t\tError: Invalid input format.\n";
                    }
                    catch (const std::out_of_range& e)
                    {
                        // This catches if the number is too large for an integer
                        cout << "\t\t\tError: Number is too large.\n";
                    }

                }
                cout << "\t\t\tError: Please enter a valid positive number for Student ID.\n";
            }

            while (true)
            {
                cout << "\t\t\tEnter Student Name: ";
                getline(cin, hashTable[index].student_name);
                if (hashTable[index].student_name.empty() || hashTable[index].student_name.length() > 50)
                {
                    cout << "\t\t\tError: Student name cannot be empty or longer than 50 characters.\n";
                    continue;
                }
                // Check if name contains only letters, spaces, and hyphens
                bool validName = true;
                for (char c : hashTable[index].student_name)
                {
                    if (!isalpha(c) && c != ' ' && c != '-')
                    {
                        validName = false;
                        break;
                    }
                }
                if (!validName)
                {
                    cout << "\t\t\tError: Name should only contain letters, spaces, and hyphens.\n";
                    continue;
                }
                break;
            }

            // Handle dates
            hashTable[index].borrowed_date = getFormattedDate("\t\t\tEnter Borrowed Date (DD/MM/YYYY): ");

            // Automatically calculate return date (7 days from borrow date)
            hashTable[index].return_date = calculateReturnDate(hashTable[index].borrowed_date);
            cout << "\t\t\tReturn Due Date set to: " << hashTable[index].return_date << endl;

            // Update book statuses only if the book was changed
            if (toupper(changeBook) == 'Y' && oldBookId != hashTable[index].book_id)
            {
                bookSystem->updateBookStatus(oldBookId, "Available");
                bookSystem->updateBookStatus(hashTable[index].book_id, "Borrowed");
            }
            cout << "\033[33;1m";
            cout << "\t\t\tBorrowing record updated successfully!\n";
            cout << "\033[0m";
        }
        else
        {
            cout << "\t\t\tBorrowing record not found!\n";
        }
    }

    //Function to delete a borrowing record
    void deleteRecord()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t -----------------------\n";
        cout << "\t\t\t|    Delete a  Record   |\n";
        cout << "\t\t\t -----------------------\n";
        cout << "\033[0m";

        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return;
        }

        int borrow_id;
        while (true)
        {
            cout << "\t\t\tEnter Borrow ID to delete: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    borrow_id = stoi(input);
                    if (borrow_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Borrow ID.\n";
        }
        //Find the borrowing record
        int index = findRecord(borrow_id);
        if (index != -1)
        {
            cout << "\033[33;1m";
            // Display record details in a formatted way
            cout << "\n\t\t\tBorrowing Record Details:\n";
            cout << "\t\t\t-------------------------\n";
            cout << "\t\t\tBorrow ID: " << hashTable[index].borrow_id << endl;
            cout << "\t\t\tStudent Details:\n";
            cout << "\t\t\tStudent ID: " << hashTable[index].student_id << endl;
            cout << "\t\t\tStudent Name: " << hashTable[index].student_name << endl;
            cout << "\t\t\tBook Details:\n";
            cout << "\t\t\tBook ID: " << hashTable[index].book_id << endl;
            cout << "\t\t\tBook Name: " << hashTable[index].book_name << endl;
            cout << "\t\t\tDates:\n";
            cout << "\t\t\tBorrowed Date: " << hashTable[index].borrowed_date << endl;
            cout << "\t\t\tReturn Due Date: " << hashTable[index].return_date << endl;
            cout << fixed << setprecision(2);
            cout << "\t\t\tFine Amount: RM " << hashTable[index].fine_amount << endl;
            cout << "\t\t\t-------------------------\n";
            cout << "\033[0m";

            // Ask for confirmation
            char confirm;
            bool validChoice = false;
            do
            {
                cout << "\n\t\t\tAre you sure you want to delete this record? (Y/N): ";
                string input;
                getline(cin >> ws, input);

                // Check if input is empty
                if(input.empty())
                {
                    cout << "\t\t\tError: Input cannot be empty. Please enter Y or N.\n";
                    continue;
                }

                // Check if input is exactly one character
                if(input.length() > 1)
                {
                    cout << "\t\t\tError: Please enter a single character (Y or N).\n";
                    continue;
                }

                // Convert to uppercase for comparison
                confirm = toupper(input[0]);

                if(confirm == 'Y' || confirm == 'N')
                {
                    validChoice = true;
                }
                else
                {
                    cout << "\t\t\tError: Invalid input. Please enter Y for Yes or N for No.\n";
                }

            } while(!validChoice);

            if (confirm == 'Y')
            {
                // Update book status back to "Available"
                bookSystem->updateBookStatus(hashTable[index].book_id, "Available");

                // Mark as unoccupied
                hashTable[index].isOccupied = false;
                currentSize--;
                cout << "\033[33;1m";
                cout << "\t\t\tRecord successfully deleted and book marked as Available.\n";
                cout << "\033[0m";
            }
            else
            {
                cout << "\033[33;1m";
                cout << "\t\t\tDeletion cancelled.\n";
                cout << "\033[0m";
            }
        }
        else
        {
            cout << "\t\t\tBorrowing record not found!\n";
        }
    }
    //Function to display all the borrowing records
    //Can sort the records based on certain attributes
    //Can display the borrowing records without sorting
    void displayAllRecord()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t ---------------------\n";
        cout << "\t\t\t|     All  Records    |\n";
        cout << "\t\t\t ---------------------\n\n";
        cout << "\033[0m";
        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return;
        }

        // Create array of pointers to records
        borrowNode** recordsArray = new borrowNode*[currentSize];
        int arrayIndex = 0;

        // Fill array with occupied records
        for (int i = 0; i < TABLE_SIZE; i++)
        {
            if (hashTable[i].isOccupied)
            {
                recordsArray[arrayIndex] = &hashTable[i];
                arrayIndex++;
            }
        }

        cout << "\t\t\tSort by:\n";
        cout << "\t\t\t1. Borrow ID\n";
        cout << "\t\t\t2. Borrowed Date\n";
        cout << "\t\t\t3. Return Due Date\n";
        cout << "\t\t\t4. No sorting\n";

        int choice;
        bool validChoice = false;
        do
        {
            cout << "\t\t\tEnter your choice (1-4): ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool isNumeric = true;
            for(char c : input)
            {
                if(!isdigit(c))
                {
                    isNumeric = false;
                    break;
                }
            }

            if(!isNumeric)
            {
                cout << "\t\t\tError: Please enter a number between 1 and 4\n";
                continue;
            }

            try
            {
                choice = stoi(input);
                if(choice < 1 || choice > 4)
                {
                    cout << "\t\t\tError: Please enter a number between 1 and 4\n";
                    continue;
                }
                validChoice = true;
            }
            catch(const std::exception& e)
            {
                cout << "\t\t\tError: Invalid input\n";
            }
        } while(!validChoice);

        if(choice != 4)
        {
            cout << "\n\t\t\tSort order:\n";
            cout << "\t\t\t1. Ascending\n";
            cout << "\t\t\t2. Descending\n";

            int orderChoice;
            bool validOrderChoice = false;
            do
            {
                cout << "\t\t\tEnter your choice (1-2): ";
                string input;
                getline(cin >> ws, input);

                // Check if input contains only digits
                bool isNumeric = true;
                for(char c : input)
                {
                    if(!isdigit(c))
                    {
                        isNumeric = false;
                        break;
                    }
                }

                if(!isNumeric)
                {
                    cout << "\t\t\tError: Please enter 1 for Ascending or 2 for Descending\n";
                    continue;
                }

                try
                {
                    orderChoice = stoi(input);
                    if(orderChoice < 1 || orderChoice > 2)
                    {
                        cout << "\t\t\tError: Please enter 1 for Ascending or 2 for Descending\n";
                        continue;
                    }
                    validOrderChoice = true;
                }
                catch(const std::exception& e)
                {
                    cout << "\t\t\tError: Invalid input\n";
                }
            } while(!validOrderChoice);

            bool isDescending = (orderChoice == 2);  //Evaluates to true if orderChoice == 2

            // Apply quick sort algorithm sorting based on choice
            switch (choice)
            {
                case 1:
                    quickSortByBorrowId(recordsArray, 0, currentSize - 1, isDescending);
                    cout << "\n\t\t\tSorted by: Borrow ID (" << (isDescending ? "Descending" : "Ascending") << ")\n";
                    break;
                case 2:
                    quickSortByBorrowDate(recordsArray, 0, currentSize - 1, isDescending);
                    cout << "\n\t\t\tSorted by: Borrowed Date (" << (isDescending ? "Descending" : "Ascending") << ")\n";
                    break;
                case 3:
                    quickSortByReturnDate(recordsArray, 0, currentSize - 1, isDescending);
                    cout << "\n\t\t\tSorted by: Return Date (" << (isDescending ? "Descending" : "Ascending") << ")\n";
                    break;
            }
        }
        else
        {
            cout << "\n\t\t\tDisplaying without sorting:\n";
        }

        cout << endl;

        cout << "\033[33;1m";
        // Display sorted records
        for (int i = 0; i < currentSize; i++)
        {
            cout << "\t\t\tBorrow ID: " << recordsArray[i]->borrow_id << endl;
            cout << "\t\t\tStudent ID: " << recordsArray[i]->student_id << endl;
            cout << "\t\t\tStudent Name: " << recordsArray[i]->student_name << endl;
            cout << "\t\t\tBook ID: " << recordsArray[i]->book_id << endl;
            cout << "\t\t\tBook Name: " << recordsArray[i]->book_name << endl;
            cout << "\t\t\tBorrowed Date: " << recordsArray[i]->borrowed_date << endl;
            cout << "\t\t\tReturn Due Date: " << recordsArray[i]->return_date << endl;
            cout << fixed << setprecision(2);
            cout << "\t\t\tFine Amount: RM " << recordsArray[i]->fine_amount << endl;
            cout << "\t\t\t---------------\n";
            endl(cout);
        }
        cout << "\033[0m";

        delete[] recordsArray;
    }
    //Function to calculate the fine amount
    float calculateFine(int borrow_id)
    {
        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return 0;
        }

        int index = findRecord(borrow_id);
        if (index != -1)
        {
            string currentDate = getCurrentDate();
            string returnDate = hashTable[index].return_date;

            int days_overdue = calculateDaysDifference(returnDate, currentDate);

            //Charge 1 ringgit fine after it is past the return due date
            if (days_overdue > 0)
            {
                cout << "\033[33;1m";
                hashTable[index].fine_amount = days_overdue * 1.0f;
                cout << fixed << setprecision(2);
                cout << "\t\t\tFine calculated: RM " << hashTable[index].fine_amount << endl;
                cout << "\t\t\tDays overdue: " << days_overdue << endl;
                cout << "\033[0m";
            }
            else
            {
                cout << "\033[33;1m";
                hashTable[index].fine_amount = 0;
                cout << "\t\t\tNo fine applicable.\n";
                cout << "\033[0m";
            }
            return hashTable[index].fine_amount;
        }
        else
        {
            cout << "\t\t\tBorrowing record not found!\n";
            return 0;
        }
    }

    //Function to manage the process of returning book
    void returnBook()
    {
        cout << "\033[33;1m";
        cout << "\t\t\t ----------------\n";
        cout << "\t\t\t|  Return Book   |\n";
        cout << "\t\t\t ----------------\n\n";
        cout << "\033[0m";

        if (currentSize == 0)
        {
            cout << "\t\t\tNo borrowing records in the system!\n";
            return;
        }

        int borrow_id;
        while (true)
        {
            cout << "\t\t\tEnter Borrow ID for the book being returned: ";
            string input;
            getline(cin >> ws, input);

            // Check if input contains only digits
            bool validInput = true;
            for (char c : input)
            {
                if (!isdigit(c))
                {
                    validInput = false;
                    break;
                }
            }

            // Convert to integer if valid
            if (validInput && !input.empty())
            {
                try
                {
                    borrow_id = stoi(input);
                    if (borrow_id > 0)
                    {
                        break;  // Valid input, exit loop
                    }
                }
                catch (const std::invalid_argument& e)
                {
                    // This catches if the string cannot be converted to an integer
                    cout << "\t\t\tError: Invalid input format.\n";
                }
                catch (const std::out_of_range& e)
                {
                    // This catches if the number is too large for an integer
                    cout << "\t\t\tError: Number is too large.\n";
                }
            }
            cout << "\t\t\tError: Please enter a valid positive number for Borrow ID.\n";
        }

        int index = findRecord(borrow_id);
        if (index != -1)
        {
            // Display record details in a formatted way
            cout << "\033[33;1m";
            cout << "\n\t\t\tBorrowing Record Details:\n";
            cout << "\t\t\t-------------------------\n";
            cout << "\t\t\tBorrow ID: " << hashTable[index].borrow_id << endl;
            cout << "\t\t\tStudent Details:\n";
            cout << "\t\t\tStudent ID: " << hashTable[index].student_id << endl;
            cout << "\t\t\tStudent Name: " << hashTable[index].student_name << endl;
            cout << "\t\t\tBook Details:\n";
            cout << "\t\t\tBook ID: " << hashTable[index].book_id << endl;
            cout << "\t\t\tBook Name: " << hashTable[index].book_name << endl;
            cout << "\t\t\tDates:\n";
            cout << "\t\t\tBorrowed Date: " << hashTable[index].borrowed_date << endl;
            cout << "\t\t\tReturn Due Date: " << hashTable[index].return_date << endl;
            cout << "\t\t\t-------------------------\n";
            cout << "\033[0m";
            // Calculate fine
            float fine = calculateFine(borrow_id);

            // Ask for confirmation before proceeding
            char confirm;
            bool validChoice = false;
            do
            {
                cout << "\n\t\t\tConfirm return of this book? (Y/N): ";
                string input;
                getline(cin >> ws, input);

                if(input.empty())
                {
                    cout << "\t\t\tError: Input cannot be empty. Please enter Y or N.\n";
                    continue;
                }

                if(input.length() > 1)
                {
                    cout << "\t\t\tError: Please enter a single character (Y or N).\n";
                    continue;
                }

                confirm = toupper(input[0]);

                if(confirm == 'Y' || confirm == 'N')
                {
                    validChoice = true;
                }
                else
                {
                    cout << "\t\t\tError: Invalid input. Please enter Y for Yes or N for No.\n";
                }
            } while(!validChoice);

            if (confirm == 'Y')
            {
                if (fine > 0)
                {
                    char payNow;
                    bool validPayChoice = false;
                    do
                    {
                        cout << "\t\t\tDoes the borrower want to pay the fine now? (Y/N): ";
                        string input;
                        getline(cin >> ws, input);

                        if(input.empty())
                        {
                            cout << "\t\t\tError: Input cannot be empty. Please enter Y or N.\n";
                            continue;
                        }

                        if(input.length() > 1)
                        {
                            cout << "\t\t\tError: Please enter a single character (Y or N).\n";
                            continue;
                        }

                        payNow = toupper(input[0]);

                        if(payNow == 'Y' || payNow == 'N')
                        {
                            validPayChoice = true;
                        }
                        else
                        {
                            cout << "\t\t\tError: Invalid input. Please enter Y for Yes or N for No.\n";
                        }
                    } while(!validPayChoice);

                    if (payNow != 'Y')
                    {
                        cout << "\033[33;1m";
                        cout << fixed << setprecision(2);
                        cout << "\t\t\tPlease note: Fine payment of RM " << fine << " is pending.\n";
                        cout << "\033[0m";
                    }
                    else
                    {
                        cout << "\033[33;1m";
                        cout << fixed << setprecision(2);
                        cout << "\t\t\tFine payment of RM " << fine << " received.\n";
                        cout << "\033[0m";
                    }
                }

                // Update book status back to "Available"
                bookSystem->updateBookStatus(hashTable[index].book_id, "Available");

                // Store book details before deleting record
                string bookName = hashTable[index].book_name;
                int bookId = hashTable[index].book_id;

                // Remove the borrowing record
                hashTable[index].isOccupied = false;
                currentSize--;
                cout << "\033[33;1m";
                cout << "\t\t\tBook '" << bookName << "' (ID: " << bookId << ") has been successfully returned.\n";
                cout << "\t\t\tBorrowing record has been removed from the system.\n";
                cout << "\033[0m";
            }
            else
            {
                cout << "\033[33;1m";
                cout << "\t\t\tReturn process cancelled.\n";
                cout << "\033[0m";
            }
        }
        else
        {
            cout << "\t\t\tBorrowing record not found! Please check the Borrow ID.\n";
        }
    }

    //Function to save the data (borrowing records) to a txt file
    void saveData()
    {
        ofstream outFile("borrowings.txt");
        if (!outFile)
        {
            return;
        }

        for (int i = 0; i < TABLE_SIZE; i++)
        {
            if (hashTable[i].isOccupied)
            {
                // Calculate current fine before saving
                string currentDate = getCurrentDate();
                int days_overdue = calculateDaysDifference(hashTable[i].return_date, currentDate);
                if (days_overdue > 0)
                {
                    hashTable[i].fine_amount = days_overdue * 1.0f;
                }
                else
                {
                    hashTable[i].fine_amount = 0;
                }
                outFile << hashTable[i].borrow_id << ","
                       << hashTable[i].student_id << ","
                       << hashTable[i].student_name << ","
                       << hashTable[i].book_id << ","
                       << hashTable[i].book_name << ","
                       << hashTable[i].borrowed_date << ","
                       << hashTable[i].return_date << ","
                       << hashTable[i].fine_amount << "\n";
            }
        }
        outFile.close();
    }
    //Function to load the data (borrowing records) from a txt file
    void loadData()
    {
        ifstream inFile("borrowings.txt");
        if (!inFile)
        {
            return;
        }

        // Reset hash table
        delete[] hashTable;
        hashTable = new borrowNode[TABLE_SIZE];
        currentSize = 0;

        string line;
        while (getline(inFile, line))
        {
            stringstream ss(line);
            string item;

            // Get borrow_id first to calculate hash
            getline(ss, item, ',');
            int borrow_id = stoi(item);

            int index = hashFunction(borrow_id);
            if (hashTable[index].isOccupied)
            {
                index = findNextSlot(index);
                if (index == -1)
                {
                    cout << "\t\t\tError: Hash table is full!\n";
                    break;
                }
            }

            hashTable[index].borrow_id = borrow_id;

            getline(ss, item, ',');
            hashTable[index].student_id = stoi(item);

            getline(ss, hashTable[index].student_name, ',');

            getline(ss, item, ',');
            hashTable[index].book_id = stoi(item);

            getline(ss, hashTable[index].book_name, ',');
            getline(ss, hashTable[index].borrowed_date, ',');
            getline(ss, hashTable[index].return_date, ',');

            getline(ss, item, ',');
            hashTable[index].fine_amount = stof(item);

            hashTable[index].isOccupied = true;
            currentSize++;

            string currentDate = getCurrentDate();
            int days_overdue = calculateDaysDifference(hashTable[index].return_date, currentDate);

            if (days_overdue > 0)
            {
                hashTable[index].fine_amount = days_overdue * 1.0f;
            }
            else
            {
                hashTable[index].fine_amount = 0;
            }
        }
        inFile.close();
        refreshBookDetails();
    }
};

#endif

