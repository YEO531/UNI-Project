public class Myprog{
public static void main (String args[]){
Account a1=new Account ("Smith",13505,100);
Account a2=new Account ("Cathy",28999,15);
Account a3=new Account ("tim",30050,2000);

a1.credit(200);
a1.withdraw(50);
a2.credit(100);
a3.credit(300);
a3.withdraw(900);
a1.printBalance();
a2.printBalance();
a3.printBalance();
System.out.println("Total number of accounts = "+Account.k);
}
}