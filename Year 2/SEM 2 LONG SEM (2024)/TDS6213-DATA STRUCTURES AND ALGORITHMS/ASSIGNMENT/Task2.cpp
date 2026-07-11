#include <iostream>
#include <string>
using namespace std;

typedef char StackItemType;  //alias char data type

class Stack
{
public:
    Stack();                    // Constructor
    Stack(const Stack& aStack); // Copy constructor
    ~Stack();                   // Destructor

    // Stack operations
    bool isEmpty() const;
    void push(const StackItemType& newItem);
    void pop();
    StackItemType getTop() const; //To get the item at the top of the stack

private:
    //Node structure for linked-list implementation
    struct StackNode
    {
        StackItemType item;   //Item stored in the node
        StackNode    *next;   //Pointer to next node
    };
    StackNode *topPtr;        //Head pointer
};

// Constructor
Stack::Stack() : topPtr(NULL)
{
}

// Copy constructor
Stack::Stack(const Stack& aStack)
{
    if (aStack.topPtr == NULL)
        topPtr = NULL;  // original list is empty
    else
    {
        // copy first node
        topPtr = new StackNode;
        topPtr->item = aStack.topPtr->item;
        // copy the remaining nodes
        StackNode *newPtr = topPtr;    // new node pointer
        for (StackNode *origPtr = aStack.topPtr->next; origPtr != NULL; origPtr = origPtr->next)
        {
            newPtr->next = new StackNode;
            newPtr = newPtr->next;
            newPtr->item = origPtr->item;
        }
        newPtr->next = NULL; // tail
    }
}

// Destructor
Stack::~Stack()
{
    while (!isEmpty())
    {
        pop();
    }
}

// Check if stack is empty
bool Stack::isEmpty() const
{
    return topPtr == NULL;
}

// Adds new item to top of stack
void Stack::push(const StackItemType& newItem)
{
    // create and initialize new node
    StackNode *newPtr = new StackNode;
    newPtr->item = newItem;
    // Link new node to current top node and update top pointer
    newPtr->next = topPtr;
    topPtr = newPtr;
}

// Remove top item from stack
void Stack::pop()
{
    if (!isEmpty())
    {
        StackNode *temp = topPtr;  //Store current top node
        topPtr = topPtr->next;
        temp->next = NULL;    // return deleted node to system
        delete temp;          //Free memory
    }
    // else: stack is empty, do nothing
}

// Get the top item
StackItemType Stack::getTop() const
{
    if (!isEmpty()) {
        return topPtr->item;
    }
    throw runtime_error("Stack is empty");  //throw exception if stack is empty
}

int precedence(char ch)  //To determine the precedence of operators
{
    if (ch == '^')
    {
        return 3;          //Return an integer value to compare which operator has higher precedence
    }
    else if (ch == '*' || ch == '/')
    {
        return 2;
    }
    else if (ch == '+' || ch == '-')
    {
        return 1;
    }
    else
    {
        return -1;
    }
}

//Convert infix expression to postfix expression
void convertPostfix(string infix)
{
    Stack characters;   //Stack to store operators
    string result;      //Store the postfix expression

    for (char ch: infix)
    {
        //Skip spaces in input
        if(ch == ' ')
        {
            continue;
        }
        //If character is an operand, add it to the result
        else if((ch >= 'a' && ch <= 'z') || (ch >= 'A' && ch <= 'Z') || (ch >= '0' && ch <= '9'))
        {
            result += ch;
        }
        //If character is '(', push it onto stack
        else if (ch == '(')
        {
            characters.push(ch);
        }
        //If character is ')', pop and add all operators to the result until '('
        else if (ch == ')')
        {
            while(characters.getTop() != '(')
            {
                result += characters.getTop();
                characters.pop();
            }
            characters.pop(); //Pop '(' from the stack
        }
        //If the character is an operator
        else
        {
            // Pop operators with higher or equal precedence and add it to the result
            while (!characters.isEmpty() && precedence(characters.getTop()) >= precedence(ch))
            {
                result += characters.getTop();
                characters.pop();
            }
            characters.push(ch); //push character into the stack
        }
    }
    // Pop any remaining operators from stack
    while(!characters.isEmpty())
    {
        result += characters.getTop();
        characters.pop();
    }
    cout << "The postfix expression: " << result << endl;  //Display the postfix expression converted
}

int main()
{
    Stack s;
    string infix_expression;
    cout << "Enter the infix expression to be converted to postfix: ";
    getline(cin, infix_expression);     //Get input for infix expression

    convertPostfix(infix_expression);   //Convert infix to postfix and display the result

    return 0;
}
