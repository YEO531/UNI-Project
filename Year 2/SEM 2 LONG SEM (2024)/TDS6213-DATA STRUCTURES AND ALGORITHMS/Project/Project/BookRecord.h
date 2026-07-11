#ifndef BOOK_RECORD_H
#define BOOK_RECORD_H

#include <iostream>
#include <fstream>
#include <sstream>
#include <limits>
using namespace std;

//Linked-list implementation
class bookRecord
{
private:

    struct bookNode
    {
        int book_id;
        string book_name;
        string book_genre;
        string book_publisher;
        int publication_year;
        string book_status;
        bookNode* next;

        bookNode()
        {
            next = nullptr;
        }
    };

    bookNode* head;

    //Convert linked list to array for sorting purposes
    bookNode** convertToArray(int& size)
    {
        // Count nodes
        size = 0;
        bookNode* current = head;
        while (current != nullptr)
        {
            size++;
            current = current->next;
        }

        // Create array of pointers
        bookNode** arr = new bookNode*[size];
        current = head;
        for (int i = 0; i < size; i++)
        {
            arr[i] = current;
            current = current->next;
        }
        return arr;
    }

    // Convert array back to linked list
    void convertToList(bookNode** arr, int size)
    {
        if (size == 0) return;

        head = arr[0];
        for (int i = 0; i < size - 1; i++)
        {
            arr[i]->next = arr[i + 1];
        }
        arr[size - 1]->next = nullptr;

        delete[] arr;
    }

     // QuickSort partition functions for different criteria
    int partitionById(bookNode** arr, int low, int high, bool descend_order)
    {
        int pivot = arr[high]->book_id;
        int i = low - 1;

        for (int j = low; j < high; j++)
        {
            if ((descend_order && arr[j]->book_id >= pivot) || (!descend_order && arr[j]->book_id <= pivot))
            {
                i++;
                swap(arr[i], arr[j]);
            }
        }
        swap(arr[i + 1], arr[high]);
        return i + 1;
    }

    int partitionByName(bookNode** arr, int low, int high, bool descend_order)
    {
        string pivot = arr[high]->book_name;
        int i = low - 1;

        for (int j = low; j < high; j++)
        {
            if ((descend_order && arr[j]->book_name >= pivot) || (!descend_order && arr[j]->book_name <= pivot))
            {
                i++;
                swap(arr[i], arr[j]);
            }
        }
        swap(arr[i + 1], arr[high]);
        return i + 1;
    }

     int partitionByYear(bookNode** arr, int low, int high, bool descend_order)
     {
        int pivot = arr[high]->publication_year;
        int i = low - 1;

        for (int j = low; j < high; j++)
        {
            if ((descend_order && arr[j]->publication_year >= pivot) || (!descend_order && arr[j]->publication_year <= pivot))
            {
                i++;
                swap(arr[i], arr[j]);
            }
        }
        swap(arr[i + 1], arr[high]);
        return i + 1;
    }

     // QuickSort implementations for different criteria
    void quickSortById(bookNode** arr, int low, int high, bool descend_order)
    {
        if (low < high)
        {
            int pi = partitionById(arr, low, high, descend_order);
            quickSortById(arr, low, pi - 1, descend_order);
            quickSortById(arr, pi + 1, high, descend_order);
        }
    }

     void quickSortByName(bookNode** arr, int low, int high, bool descend_order)
     {
        if (low < high)
        {
            int pi = partitionByName(arr, low, high, descend_order);
            quickSortByName(arr, low, pi - 1, descend_order);
            quickSortByName(arr, pi + 1, high, descend_order);
        }
     }

      void quickSortByYear(bookNode** arr, int low, int high, bool descend_order)
      {
        if (low < high)
        {
            int pi = partitionByYear(arr, low, high, descend_order);
            quickSortByYear(arr, low, pi - 1, descend_order);
            quickSortByYear(arr, pi + 1, high, descend_order);
        }
      }

public:
    bookRecord()
    {
        head = nullptr;
    }
    ~bookRecord()
    {
        while(head != nullptr)
        {
            bookNode* temp = head;
            head = head->next;
            delete temp;
        }
    }
    void addBook()
    {
        bookNode* newNode = new bookNode();
        cout << "\t\t\t ------------------\n";
        cout << "\t\t\t|   Adding a Book  |\n";
        cout << "\t\t\t ------------------\n\n";

        // Book ID validation
        bool validId = false;
        do
        {
            cout << "\t\t\tEnter Book ID: ";
            string input;
            getline(cin >> ws, input);

            // Check if the input string contains only digits
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
                cout << "\t\t\tError: Book ID must contain only numbers\n";
                continue;
            }

            // Convert string to integer
            try
            {
                newNode->book_id = stoi(input);
                if(newNode->book_id <= 0)
                {
                    cout << "\t\t\tError: Book ID must be a positive number\n";
                    continue;
                }
                validId = true;
            }
            catch(const std::invalid_argument& e)
            {
                cout << "\t\t\tError: Invalid number format\n";
            }
            catch(const std::out_of_range& e)
            {
                cout << "\t\t\tError: Number is too large\n";
            }

        }while (!validId);

        //Check whether an id already exists
        bookNode* current = head;
        while (current != nullptr)
        {
            if(current->book_id == newNode->book_id)
            {
                cout << "\t\t\tThis Book ID already exists!\n";
                delete newNode;
                return;
            }
            current = current->next;
        }

        cout << "\t\t\tEnter Book Name: ";
        getline(cin >> ws, newNode->book_name);

        while (newNode->book_name.empty() || newNode->book_name.length() > 100)
        {
            cout << "\t\t\tError: Book name cannot be empty or longer than 100 characters\n";
            cout << "\t\t\tEnter Book Name: ";
            getline(cin >> ws, newNode->book_name);
        }

        cout << "\n\t\t\tAvailable genres:\n";
        cout << "\t\t\t- Sci-Fi\n";
        cout << "\t\t\t- Romance\n";
        cout << "\t\t\t- Non-Fiction\n";
        cout << "\t\t\t- Crime\n";
        cout << "\t\t\t- Mystery\n";
        cout << "\t\t\t- Fantasy\n";
        cout << "\t\t\t- History\n";
        cout << "\t\t\t- Biography\n";

        bool validGenre = false;
        do
        {
            cout << "\t\t\tEnter Book Genre: ";
            cin >> newNode->book_genre;

            if(newNode->book_genre == "Sci-Fi" ||
                newNode->book_genre == "Romance" ||
                newNode->book_genre == "Non-Fiction" ||
                newNode->book_genre == "Crime" ||
                newNode->book_genre == "Mystery" ||
                newNode->book_genre == "Fantasy" ||
                newNode->book_genre == "History" ||
                newNode->book_genre == "Biography")
            {
                validGenre = true;
            }
            else
            {
                cout << "\t\t\tError: Invalid genre. Please select from the available genres.\n";
            }
        } while(!validGenre);


        cout << "\t\t\tEnter Book Publisher: ";
        getline(cin >> ws, newNode->book_publisher);
        while (newNode->book_publisher.empty() || newNode->book_publisher.length() > 100)
        {
            cout << "\t\t\tError: Publisher cannot be empty or longer than 100 characters\n";
            cout << "\t\t\tEnter Book Publisher: ";
            getline(cin >> ws, newNode->book_publisher);
        }

        bool validYear = false;
        do
        {
            cout << "\t\t\tEnter publication year: ";
            string input;
            getline(cin >> ws, input);

            // Check if the input string contains only digits
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
                cout << "\t\t\tError: Year must contain only numbers\n";
                continue;
            }

            // Convert string to integer
            try
            {
                newNode->publication_year = stoi(input);
                if(newNode->publication_year < 1000 || newNode->publication_year > 2025)
                {
                    cout << "\t\t\tError: Please enter a year between 1000 and 2025\n";
                    continue;
                }
                validYear = true;
            }
            catch(const std::invalid_argument& e)
            {
                cout << "\t\t\tError: Invalid year format\n";
            }
            catch(const std::out_of_range& e)
            {
                cout << "\t\t\tError: Year is too large\n";
            }

        } while (!validYear);

        // Automatically set status to "Available"
        newNode->book_status = "Available";
        cout << "\t\t\tBook status: " << newNode->book_status;

        newNode->next = head;
        head = newNode;
        cout << "\n\t\t\tBook added sucessfully!!";
    }
     bool isBookAvailable(int bookId)
     {
        bookNode* current = head;
        while (current != nullptr)
        {
            if (current->book_id == bookId)
            {
                return (current->book_status == "Available");
            }
            current = current->next;
        }
        return false;
    }

    string getBookName(int bookId, bool& found)
    {
        bookNode* current = head;
        while (current != nullptr)
        {
            if (current->book_id == bookId)
            {
                found = true;
                return current->book_name;
            }
            current = current->next;
        }
        found = false;
        return "";
    }

    void updateBookStatus(int bookId, const string& newStatus)
    {
        bookNode* current = head;
        while (current != nullptr)
        {
            if (current->book_id == bookId)
            {
                current->book_status = newStatus;
                return;
            }
            current = current->next;
        }
    }
    void searchBook()
    {
        cout << "\t\t\t -------------------------\n";
        cout << "\t\t\t|   Searching for a Book  |\n";
        cout << "\t\t\t -------------------------\n\n";
        if(head == nullptr)
        {
            cout << "\t\t\tNo books in the system.\n";
            return;
        }

        int id;
        bool validId = false;
        do
        {
            cout << "\t\t\tEnter Book ID to search: ";
            string input;
            getline(cin >> ws, input);

            // Check if the input string contains only digits
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
                cout << "\t\t\tError: Book ID must contain only numbers\n";
                continue;
            }

            // Convert string to integer
            try
            {
                id = stoi(input);
                if(id <= 0)
                {
                    cout << "\t\t\tError: Book ID must be a positive number\n";
                    continue;
                }
                validId = true;
            }
            catch(const std::invalid_argument& e)
            {
                cout << "\t\t\tError: Invalid number format\n";
            }
            catch(const std::out_of_range& e)
            {
                cout << "\t\t\tError: Number is too large\n";
            }
        }while (!validId);

        bookNode* current = head;
        while(current != nullptr)
        {
            if(current->book_id == id)
            {
                cout << "\t\t\tBook Found!\n\n";
                cout << "\t\t\tID: " << current->book_id << endl;
                cout << "\t\t\tName: " << current->book_name << endl;
                cout << "\t\t\tGenre: " << current->book_genre << endl;
                cout << "\t\t\tPublisher: " << current->book_publisher << endl;
                cout << "\t\t\tPublication year: " << current->publication_year << endl;
                cout << "\t\t\tStatus: " << current->book_status << endl;
                return;
            }
            current = current->next;
        }
        cout << "\t\t\tBook not found!\n";
    }



    void displayAllBooks()
    {
        if(head == nullptr)
        {
            cout << "\t\t\tNo books in the system.\n";
            return;
        }
        cout << "\t\t\t ----------------------\n";
        cout << "\t\t\t|   All Book Records   |\n";
        cout << "\t\t\t ----------------------\n\n";

        cout << "\t\t\tSort by:\n";
        cout << "\t\t\t1. Book ID\n";
        cout << "\t\t\t2. Book Name\n";
        cout << "\t\t\t3. Publication Year\n";
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

        if (choice == 4) // No sorting
        {
            bookNode* current = head;
            cout << "\n\t\t\tDisplaying without sorting:\n";
            while (current != nullptr)
            {
                cout << "\n\t\t\tID: " << current->book_id << endl;
                cout << "\t\t\tName: " << current->book_name << endl;
                cout << "\t\t\tGenre: " << current->book_genre << endl;
                cout << "\t\t\tPublisher: " << current->book_publisher << endl;
                cout << "\t\t\tPublication year: " << current->publication_year << endl;
                cout << "\t\t\tStatus: " << current->book_status << endl;
                cout << "\t\t\t---------------\n";
                current = current->next;
            }
            return;
        }


        int size;
        bookNode** arr = convertToArray(size);

        cout << "\n\t\t\tSort order:\n";
        cout << "\t\t\t1. Ascending\n";
        cout << "\t\t\t2. Descending\n";

        int orderChoice;
        bool validOrderChoice = false;
        do
        {
            cout << "\t\t\tEnter your choice: ";
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

        bool descending = (orderChoice == 2);

        // Apply sorting based on choice
        switch(choice)
        {
            case 1:
                quickSortById(arr, 0, size - 1, descending);
                cout << (descending ? "\n\t\t\tSorted by Book ID (Descending):\n" : "\t\t\tSorted by Book ID (Ascending):\n");
                break;
            case 2:
                quickSortByName(arr, 0, size - 1, descending);
                cout << (descending ? "\n\t\t\tSorted by Book Name (Descending):\n" : "\t\t\tSorted by Book Name (Ascending):\n");
                break;
            case 3:
                quickSortByYear(arr, 0, size - 1, descending);
                cout << (descending ? "\n\t\t\tSorted by Publication Year (Descending):\n" : "\t\t\tSorted by Publication Year (Ascending):\n");
                break;
        }

         // Display the books
        for(int i = 0; i < size; i++)
        {
            cout << "\n\t\t\tID: " << arr[i]->book_id << endl;
            cout << "\t\t\tName: " << arr[i]->book_name << endl;
            cout << "\t\t\tGenre: " << arr[i]->book_genre << endl;
            cout << "\t\t\tPublisher: " << arr[i]->book_publisher << endl;
            cout << "\t\t\tPublication year: " << arr[i]->publication_year << endl;
            cout << "\t\t\tStatus: " << arr[i]->book_status << endl;
            cout << "\t\t\t---------------\n";
        }

        convertToList(arr, size);
    }
    void editBook()
    {
        cout << "\t\t\t -----------------------\n";
        cout << "\t\t\t|    Edit Book Info     |\n";
        cout << "\t\t\t -----------------------\n\n";

        if (head == nullptr)
        {
            cout << "\t\t\tNo books in the system.\n";
            return;
        }
        int id;
        bool validId = false;
        do
        {
            cout << "\t\t\tEnter Book ID to edit: ";
            string input;
            getline(cin >> ws, input);

            // Check if the input string contains only digits
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
                cout << "\t\t\tError: Book ID must contain only numbers\n";
                continue;
            }

            // Convert string to integer
            try
            {
                id = stoi(input);
                if(id <= 0)
                {
                    cout << "\t\t\tError: Book ID must be a positive number\n";
                    continue;
                }
                validId = true;
            }
            catch(const std::invalid_argument& e)
            {
                cout << "\t\t\tError: Invalid number format\n";
            }
            catch(const std::out_of_range& e)
            {
                cout << "\t\t\tError: Number is too large\n";
            }
        }while (!validId);

        bookNode* current = head;

        while (current != nullptr)
        {
            if (current->book_id == id)
            {
                cout << "\t\t\tCurrent Book Details:\n";
                cout << "\t\t\tName: " << current->book_name << endl;
                cout << "\t\t\tGenre: " << current->book_genre << endl;
                cout << "\t\t\tPublisher: " << current->book_publisher << endl;
                cout << "\t\t\tPublication Year: " << current->publication_year << endl;

                cout << "\n\t\t\tEnter new details:\n";
                cout << "\t\t\tEnter Book Name: ";
                getline(cin, current->book_name);
                while (current->book_name.empty() || current->book_name.length() > 100)
                {
                    cout << "\t\t\tError: Book name cannot be empty or longer than 100 characters\n";
                    cout << "\t\t\tEnter Book Name: ";
                    getline(cin, current->book_name);
                }

                cout << "\t\t\tAvailable genres:\n";
                cout << "\t\t\t- Sci-Fi\n";
                cout << "\t\t\t- Romance\n";
                cout << "\t\t\t- Non-Fiction\n";
                cout << "\t\t\t- Crime\n";
                cout << "\t\t\t- Mystery\n";
                cout << "\t\t\t- Fantasy\n";
                cout << "\t\t\t- History\n";
                cout << "\t\t\t- Biography\n";

                bool validGenre = false;
                do
                {
                    cout << "\t\t\tEnter Book Genre: ";
                    getline(cin, current->book_genre);

                    if(current->book_genre == "Sci-Fi" ||
                        current->book_genre == "Romance" ||
                        current->book_genre == "Non-Fiction" ||
                        current->book_genre == "Crime" ||
                        current->book_genre == "Mystery" ||
                        current->book_genre == "Fantasy" ||
                        current->book_genre == "History" ||
                        current->book_genre == "Biography")
                    {
                        validGenre = true;
                    }
                    else
                    {
                        cout << "\t\t\tError: Invalid genre. Please select from the available genres.\n";
                    }
                } while(!validGenre);

                cout << "\t\t\tEnter Book Publisher: ";
                getline(cin, current->book_publisher);
                while (current->book_publisher.empty() || current->book_publisher.length() > 100)
                {
                    cout << "\t\t\tError: Publisher cannot be empty or longer than 100 characters\n";
                    cout << "\t\t\tEnter Book Publisher: ";
                    getline(cin, current->book_publisher);
                }

                 // Publication year validation
                bool validYear = false;
                do
                {
                    cout << "\t\t\tEnter publication year: ";
                    string input;
                    getline(cin >> ws, input);

                    // Check if the input string contains only digits
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
                        cout << "\t\t\tError: Year must contain only numbers\n";
                        continue;
                    }

                    // Convert string to integer
                    try
                    {
                        current->publication_year = stoi(input);
                        if(current->publication_year < 1000 || current->publication_year > 2025)
                        {
                            cout << "\t\t\tError: Please enter a year between 1000 and 2025\n";
                            continue;
                        }
                        validYear = true;
                    }
                    catch(const std::invalid_argument& e)
                    {
                        cout << "\t\t\tError: Invalid year format\n";
                    }
                    catch(const std::out_of_range& e)
                    {
                        cout << "\t\t\tError: Year is too large\n";
                    }

                }while (!validYear);

                cout << "\t\t\tBook information updated successfully!\n";
                return;
            }
            current = current->next;
        }
        cout << "\t\t\tBook not found!\n";
    }
    void deleteBook()
    {
        cout << "\t\t\t -----------------------\n";
        cout << "\t\t\t|    Delete a Book     |\n";
        cout << "\t\t\t -----------------------\n\n";
        if(head == nullptr)
        {
            cout << "\t\t\tNo books in the system.\n";
            return;
        }
        int id;
        bool validId = false;
        do
        {
            cout << "\t\t\tEnter Book ID to delete: ";
            string input;
            getline(cin >> ws, input);

            // Check if the input string contains only digits
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
                cout << "\t\t\tError: Book ID must contain only numbers\n";
                continue;
            }

            // Convert string to integer
            try
            {
                id = stoi(input);
                if(id <= 0)
                {
                    cout << "\t\t\tError: Book ID must be a positive number\n";
                    continue;
                }
                validId = true;
            }
            catch(const std::invalid_argument& e)
            {
                cout << "\t\t\tError: Invalid number format\n";
            }
            catch(const std::out_of_range& e)
            {
                cout << "\t\t\tError: Number is too large\n";
            }
        }while (!validId);

        // Check if book is borrowed (status is "Borrowed")
        bookNode* current = head;
        bool bookFound = false;
        while(current != nullptr)
        {
            if(current->book_id == id)
            {
                cout << "\n\t\t\tBook Found! Here are the details:\n";
                cout << "\t\t\t--------------------------------\n";
                cout << "\t\t\tID: " << current->book_id << endl;
                cout << "\t\t\tName: " << current->book_name << endl;
                cout << "\t\t\tGenre: " << current->book_genre << endl;
                cout << "\t\t\tPublisher: " << current->book_publisher << endl;
                cout << "\t\t\tPublication year: " << current->publication_year << endl;
                cout << "\t\t\tStatus: " << current->book_status << endl;
                cout << "\t\t\t--------------------------------\n";

                if(current->book_status == "Borrowed")
                {
                    cout << "\t\t\tError: Cannot delete book as it is currently borrowed.\n";
                    cout << "\t\t\tPlease ensure the book is returned before deletion.\n";
                    return;
                }

                char confirm;
                bool validChoice = false;
                do
                {
                    cout << "\n\t\t\tAre you sure you want to delete this book? (Y/N): ";
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

                if(confirm != 'Y')
                {
                    cout << "\t\t\tDeletion cancelled.\n";
                    return;
                }

                bookFound = true;
                break;
            }
            current = current->next;
        }

        if(!bookFound)
        {
            cout << "\t\t\tBook not found!\n";
            return;
        }

        if(head->book_id == id)
        {
            bookNode* temp = head;
            head = head->next;
            delete temp;
            cout << "\t\t\tBook deleted successfully!\n";
            return;
        }
        current = head;
        while (current->next != nullptr)
        {
            if (current->next->book_id == id)
            {
                bookNode* temp = current->next;
                current->next = current->next->next;
                delete temp;
                cout << "\t\t\tBook deleted successfully!\n";
                return;
            }
            current = current->next;
        }
    }

    void saveData()
    {
        ofstream outFile("books.txt");
        if (!outFile)
        {
            //cout << "\t\t\tError: Could not open file for writing!\n";
            return;
        }

        bookNode* current = head;
        while (current != nullptr)
        {
            outFile << current->book_id << ","
                   << current->book_name << ","
                   << current->book_genre << ","
                   << current->book_publisher << ","
                   << current->publication_year << ","
                   << current->book_status << "\n";
            current = current->next;
        }
        outFile.close();
        //cout << "\t\t\tBooks data saved successfully!\n";
    }
    void loadData() {
        ifstream inFile("books.txt");
        if (!inFile)
        {
            //cout << "\t\t\tNo previous book records found.\n";
            return;
        }

        // Clear existing records
        while (head != nullptr)
        {
            bookNode* temp = head;
            head = head->next;
            delete temp;
        }

        string line;
        while (getline(inFile, line))
        {
            stringstream ss(line);
            string item;
            bookNode* newNode = new bookNode();

            // Parse each field using comma as delimiter
            getline(ss, item, ',');
            newNode->book_id = stoi(item);

            getline(ss, newNode->book_name, ',');
            getline(ss, newNode->book_genre, ',');
            getline(ss, newNode->book_publisher, ',');

            getline(ss, item, ',');
            newNode->publication_year = stoi(item);

            getline(ss, newNode->book_status, ',');

            // Add to front of list
            newNode->next = head;
            head = newNode;
        }
        inFile.close();
        //cout << "\t\t\tBooks data loaded successfully!\n";
    }

};

#endif








































