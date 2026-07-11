#include <iostream>
#include <cstdlib>
#include <limits>
#include <ctime>
#include <stdexcept>
#include "BorrowRecord.h"
#include "BookRecord.h"
#include "User.h"
using namespace std;

void main_menu();
void book_menu();
void borrow_menu();

int main()
{
    bookRecord bookSystem;                     //bookRecord ADT
    borrowingRecord borrowSystem(&bookSystem); //borrowingRecord ADT
    User userSystem;                           //User ADT
    int option, book_choice, borrow_choice;
    string input;

    userSystem.loadUsers();                    //Load data
    userSystem.showMenu();
    if (userSystem.getExit()) // Check if user chose to exit
    {
        cout << "\033[33m";
        cout << "\n\t\t\t        Exit Successfully. Have a Nice Day!";
        cout << "\033[0m";
        return 0;
    }
    system("cls");

    bookSystem.loadData();
    borrowSystem.loadData();

    do
    {
        main_menu();
        cout <<  "\033[36;1m";
        cout << "\t\t\t     Enter your choice(1-3): ";
        try
        {
            cin >> input;

            if(cin.fail())
            {
                throw runtime_error("Input stream error");
            }

            // Check if input contains only digits
            if(input.find_first_not_of("0123456789") != string::npos)
            {
                throw invalid_argument("Non-numeric input");
            }

            option = stoi(input);
            if(option < 1 || option > 3)
            {
                throw out_of_range("Choice must be between 1 and 3");
            }
            cout << "\033[0m";
            if(option == 1)
            {
                do
                {
                    //Handles book records
                    system("cls");
                    book_menu();
                    cout <<  "\033[36;1m";
                    cout << "\t\t\t     Enter your choice(1-6): ";
                    try                                                //Exception handling
                    {
                        cin >> input;

                        if(cin.fail())
                        {
                            throw runtime_error("Input stream error");
                        }

                        if(input.find_first_not_of("0123456789") != string::npos)
                        {
                            throw invalid_argument("Non-numeric input");
                        }

                        book_choice = stoi(input);
                        if(book_choice < 1 || book_choice > 6)
                        {
                            throw out_of_range("Choice must be between 1 and 6");
                        }
                        cout << "\033[0m";

                        switch(book_choice)
                        {
                        case 1:
                            system("cls");
                            bookSystem.addBook();
                            bookSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;

                        case 2:
                            system("cls");
                            bookSystem.searchBook();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 3:
                            system("cls");
                            bookSystem.displayAllBooks();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 4:
                            system("cls");
                            bookSystem.editBook();
                            bookSystem.saveData();
                            borrowSystem.refreshBookDetails();    //refresh book details if there is changes
                            borrowSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 5:
                            system("cls");
                            bookSystem.deleteBook();
                            bookSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 6:
                            system("cls");
                            break;
                        }
                    }
                    catch(const invalid_argument&)
                    {
                        cout << "\t\t\t     Invalid input! Please enter a number.\n";
                        cout << "\t\t\t     Press any key to continue . . .";
                        cin.clear();
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                    catch(const out_of_range&)
                    {
                        cout << "\t\t\t     Please enter a number between 1 and 6!\n";
                        cout << "\t\t\t     Press any key to continue . . .";
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                    catch(const runtime_error&)
                    {
                        cout << "\t\t\t     Input error! Please try again.\n";
                        cout << "\t\t\t     Press any key to continue . . .";
                        cin.clear();
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                }while(book_choice != 6);
            }
            else if(option == 2)
            {
                do
                {
                    //Handles borrowing records
                    system("cls");
                    borrow_menu();
                    cout <<  "\033[36;1m";
                    cout << "\t\t\t    Enter your choice(1-7): ";
                    try
                    {
                        cin >> input;

                        if(cin.fail())
                        {
                            throw runtime_error("Input stream error");
                        }

                        if(input.find_first_not_of("0123456789") != string::npos)
                        {
                            throw invalid_argument("Non-numeric input");
                        }

                        borrow_choice = stoi(input);
                        if(borrow_choice < 1 || borrow_choice > 7)
                        {
                            throw out_of_range("Choice must be between 1 and 7");
                        }
                        cout << "\033[0m";
                        switch(borrow_choice)
                        {
                        case 1:
                            system("cls");
                            borrowSystem.addRecord();
                            borrowSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 2:
                            system("cls");
                            borrowSystem.searchRecord();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 3:
                            system("cls");
                            borrowSystem.displayAllRecord();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 4:
                            system("cls");
                            borrowSystem.editRecord();
                            borrowSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 5:
                            system("cls");
                            borrowSystem.deleteRecord();
                            borrowSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 6:
                            system("cls");
                            borrowSystem.returnBook(); //function to return a book
                            borrowSystem.saveData();
                            cout << "\n\t\t\t";
                            system("PAUSE");
                            break;
                        case 7:
                            system("cls");
                            break;
                        }
                    }
                    catch(const invalid_argument&)
                    {
                        cout << "\t\t\t    Invalid input! Please enter a number.\n";
                        cout << "\t\t\t    Press any key to continue . . .";
                        cin.clear();
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                    catch(const out_of_range&)
                    {
                        cout << "\t\t\t    Please enter a number between 1 and 7!\n";
                        cout << "\t\t\t    Press any key to continue . . .";
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                    catch(const runtime_error&)
                    {
                        cout << "\t\t\t    Input error! Please try again.\n";
                        cout << "\t\t\t    Press any key to continue . . .";
                        cin.clear();
                        cin.ignore(numeric_limits<streamsize>::max(), '\n');
                        cin.get();
                        system("cls");
                        continue;
                    }
                }while(borrow_choice != 7);
            }
        }
        catch(const invalid_argument&)
        {
            cout << "\t\t\t     Invalid input! Please enter a number.\n";
            cout << "\t\t\t     Press any key to continue . . .";
            cin.clear();
            cin.ignore(numeric_limits<streamsize>::max(), '\n');
            cin.get();
            system("cls");
            continue;
        }
        catch(const out_of_range&)
        {
            cout << "\t\t\t     Please enter a number between 1 and 3!\n";
            cout << "\t\t\t     Press any key to continue . . .";
            cin.ignore(numeric_limits<streamsize>::max(), '\n');
            cin.get();
            system("cls");
            continue;
        }
        catch(const runtime_error&)
        {
            cout << "\t\t\t     Input error! Please try again.\n";
            cout << "\t\t\t     Press any key to continue . . .";
            cin.clear();
            cin.ignore(numeric_limits<streamsize>::max(), '\n');
            cin.get();
            system("cls");
            continue;
        }
    }while(option != 3);

    cout << "\033[33m";
    cout << "\n\t\t\t     Exit Successfully. Have a Nice Day!";
    cout << "\033[0m";

    userSystem.saveUsers();     //Save data
    bookSystem.saveData();
    borrowSystem.saveData();

    return 0;
}

//Menus
void main_menu()
{
    cout << "\033[33;1m";
    cout << "\t\t\t  -----------------------------\n";
    cout << "\t\t\t |  Library Management System  |\n";
    cout << "\t\t\t  -----------------------------\n\n";
    cout << "\t\t\t     -----------------------\n";
    cout << "\t\t\t    | 1. Book Records       |\n";
    cout << "\t\t\t    | 2. Borrowing Records  |\n";
    cout << "\t\t\t    | 3. Exit               |\n";
    cout << "\t\t\t     -----------------------\n";
    cout << "\033[0m";
}

void book_menu()
{
    cout << "\033[33;1m";
    cout << "\t\t\t          --------------\n";
    cout << "\t\t\t         |   Book Menu  |\n";
    cout << "\t\t\t          --------------\n";
    cout << "\t\t\t   ----------------------------\n";
    cout << "\t\t\t  | 1. Add a Book              |\n";
    cout << "\t\t\t  | 2. Search a Book           |\n";
    cout << "\t\t\t  | 3. Display all Books       |\n";
    cout << "\t\t\t  | 4. Edit a Book Record      |\n";
    cout << "\t\t\t  | 5. Delete a Book Record    |\n";
    cout << "\t\t\t  | 6. Exit                    |\n";
    cout << "\t\t\t   ----------------------------\n";
    cout << "\033[0m";
}

void borrow_menu()
{
    cout << "\033[33;1m";
    cout << "\t\t\t          --------------\n";
    cout << "\t\t\t         |  Borrow Menu |\n";
    cout << "\t\t\t          --------------\n";
    cout << "\t\t\t   ----------------------------\n";
    cout << "\t\t\t  | 1. Add a Record            |\n";
    cout << "\t\t\t  | 2. Search a Record         |\n";
    cout << "\t\t\t  | 3. Display all Records     |\n";
    cout << "\t\t\t  | 4. Edit a Borrowing Record |\n";
    cout << "\t\t\t  | 5. Delete a Record         |\n";
    cout << "\t\t\t  | 6. Return Book             |\n";
    cout << "\t\t\t  | 7. Exit                    |\n";
    cout << "\t\t\t   ----------------------------\n";
    cout << "\033[0m";
}


