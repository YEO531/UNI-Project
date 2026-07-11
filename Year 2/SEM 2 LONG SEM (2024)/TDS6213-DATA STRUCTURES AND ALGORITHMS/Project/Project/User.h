#ifndef USER_H
#define USER_H

#include <iostream>
#include <fstream>
#include <ostream>
#include <limits>
#include <conio.h>
using namespace std;

const int MAX_USER = 100;

class User
{
   private:
       string username;
       string password;
       bool Exit;

       User* next;

       static User* head;
       static const string user_file;

       bool isValidMenuChoice(const string& input)
       {
            // Check if input is exactly one character
            if (input.length() != 1)
            {
                return false;
            }
            // Check if the character is 1, 2, or 3
            char choice = input[0];
            return choice >= '1' && choice <= '3';
        }

       bool isUsernameAvailable(const string& username)
       {
            User* current = head;
            while (current != nullptr)
            {
                if (current->username == username)
                {
                    return false;
                }
                current = current->next;
            }
            return true;
       }

        // New validation helper functions
       bool isValidUsername(const string& username)
       {
           // Username requirements:
           // 1. 3-20 characters long
           // 2. Only alphanumeric characters and underscores
           // 3. Must start with a letter
           if (username.length() < 3 || username.length() > 20)
               return false;

           if (!isalpha(username[0]))
               return false;

           for (char c : username)
           {
               if (!isalnum(c) && c != '_')
                   return false;
           }
           return true;
       }

       string getMaskedPassword()
       {
           string password;
           char ch;
           while ((ch = _getch()) != '\r') // Read until Enter is pressed
           {
                if (ch == 8)   // Handle backspace
                {
                    if (!password.empty())
                    {
                        password.pop_back();
                        cout << "\b \b"; // Erase the last * displayed
                    }
                }
                else
                {
                    password += ch;
                    cout << '*';
                }
            }
            cout << endl;
            return password;
        }

       bool isValidPassword(const string& password)
       {
           // Password requirements:
           // 1. At least 8 characters long
           // 2. Contains at least one uppercase letter
           // 3. Contains at least one lowercase letter
           // 4. Contains at least one number
           // 5. No spaces allowed
           if (password.length() < 8)
               return false;

           bool hasUpper = false;
           bool hasLower = false;
           bool hasDigit = false;

           for (char c : password)
           {
               if (isspace(c))
                   return false;
               if (isupper(c))
                   hasUpper = true;
               if (islower(c))
                   hasLower = true;
               if (isdigit(c))
                   hasDigit = true;
           }

           return hasUpper && hasLower && hasDigit;
       }

   public:
        User() : next(nullptr), Exit(false) {}

        ~User()
        {
             while (head != nullptr)
             {
                User* temp = head;
                head = head->next;
                temp->next = nullptr;  // Explicitly nullify the pointer
                delete temp;
             }
        }

        bool getExit() const { return Exit; }

        bool verifyLogin(const string& username, const string& password)
        {
            User* current = head;
            while (current != nullptr)
            {
                if (current->username == username && current->password == password)
                {
                    return true;
                }
                current = current->next;
            }
            return false;
        }

        void addUser(const std::string& username, const std::string& password)
        {
            User* newUser = new User();
            newUser->username = username;
            newUser->password = password;
            newUser->next = nullptr;

            if (head == nullptr)
            {
                head = newUser;
            }
            else
            {
                User* current = head;
                while (current->next != nullptr)
                {
                    current = current->next;
                }
                current->next = newUser;
            }
        }

        void loadUsers()
        {
            ifstream file(user_file);
            if (!file) return;

            string line;
            while (getline(file, line))
            {
                string username, password;
                size_t pos = line.find(",");
                if (pos != std::string::npos)
                {
                    username = line.substr(0, pos);
                    password = line.substr(pos + 1);
                    addUser(username, password);
                }
            }
            file.close();
        }
       void saveUsers()
       {
            ofstream file(user_file);
            User* current = head;

            while (current != nullptr)
            {
                file << current->username << "," << current->password << endl;
                current = current->next;
            }
            file.close();
       }
        bool registerUser()
        {
            string username, password;
            bool validInput = false;

            cout << "\n\t\t\t=== User Registration ===\n";

            // Username validation loop
            do
            {
                cout << "\t\t\tEnter username (3-20 characters, start with a letter): ";
                getline(cin, username);

                if (!isValidUsername(username))
                {
                    cout << "\t\t\tInvalid username format. Requirements:\n"
                         << "\t\t\t- Must be 3-20 characters long\n"
                         << "\t\t\t- Must start with a letter\n"
                         << "\t\t\t- Can only contain letters, numbers, and underscores\n";
                    continue;
                }

                if (!isUsernameAvailable(username))
                {
                    cout << "\t\t\tUsername already exists. Please choose another.\n";
                    continue;
                }

                validInput = true;
            } while (!validInput);

            // Password validation loop
            validInput = false;
            do
            {
                cout << "\t\t\tEnter password: ";
                password = getMaskedPassword();

                if (!isValidPassword(password))
                {
                    cout << "\t\t\tInvalid password format. Requirements:\n"
                         << "\t\t\t- Must be at least 8 characters long\n"
                         << "\t\t\t- Must contain at least one uppercase letter\n"
                         << "\t\t\t- Must contain at least one lowercase letter\n"
                         << "\t\t\t- Must contain at least one number\n"
                         << "\t\t\t- No spaces allowed\n";
                    continue;
                }

                validInput = true;
            } while (!validInput);

            addUser(username, password);
            saveUsers();

            cout << "\n\t\t\tRegistration successful!\n";
            return true;
        }

        // Menu function
       void showMenu()
       {
            string input, username, password;
            bool validChoice;

            while (true)
            {
                cout << "\n\t\t\t\t   Library Management System\n";
                cout << "\n\t\t\t\t -----------------------------\n";
                cout << "\t\t\t\t|1. New User Registration     |\n";
                cout << "\t\t\t\t|2. Login                     |\n";
                cout << "\t\t\t\t|3. Exit                      |\n";
                cout << "\t\t\t\t -----------------------------\n";
                do
                {
                    cout << "\t\t\t\tEnter your choice (1-3): ";
                    getline(cin, input);

                    validChoice = isValidMenuChoice(input);

                    if (!validChoice)
                    {
                        cout << "\t\t\t\tInvalid input. Please enter 1, 2, or 3.\n";
                    }
                } while (!validChoice);

                int choice = input[0] - '0';

                switch (choice)
                {
                    case 1:
                        system("cls");
                        registerUser();
                        cout << "\n\t\t\t";
                        system("PAUSE");
                        system("cls");
                        break;

                    case 2:
                        system("cls");
                        cout << "\n\t\t\t\t=== Login ===\n";
                        cout << "\t\t\t\tEnter username: ";
                        getline(cin, username);
                        cout << "\t\t\t\tEnter password: ";
                        password = getMaskedPassword();

                        if (verifyLogin(username, password))
                        {
                            cout << "\n\t\t\t\tLogin Successfully!\n";
                            cout << "\t\t\t\t";
                            system("PAUSE");
                            return;
                        }
                        else
                        {
                            cout << "\n\t\t\t\tInvalid username or password! Access Denied!\n";
                            cout << "\t\t\t\t";
                            system("PAUSE");
                            system("cls");

                        }
                        break;

                    case 3:
                        Exit = true;
                        return;

                    default:
                        //cout << "\n\t\t\t\tInvalid choice. Please try again.\n";
                        system("cls");
                }
            }
        }
};
User* User::head = nullptr;
const string User::user_file = "users.txt";


#endif
