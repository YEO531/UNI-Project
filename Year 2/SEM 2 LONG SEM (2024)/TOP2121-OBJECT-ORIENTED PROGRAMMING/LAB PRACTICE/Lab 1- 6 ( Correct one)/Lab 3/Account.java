public class Account {
double balance;
int number;
String name;
public static int k = 0;
Account (String s, int num, double bal){
balance=bal;
number=num;
name=s;
k++;
}
void credit (double amount){
balance=balance+amount;
}
void withdraw(double amount){
balance=balance-amount;
}
void printBalance(){
System.out.println("Balance amount "+name+" in Account no: "+number+" is RM "+balance);
}
}