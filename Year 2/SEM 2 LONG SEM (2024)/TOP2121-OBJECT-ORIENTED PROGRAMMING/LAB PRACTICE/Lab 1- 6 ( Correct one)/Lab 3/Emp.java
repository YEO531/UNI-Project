import java.util.Date;

public class Emp {
private String fname;
private String lname;
private int sal;
private Date start;

public Emp() {
init ("unknown", "unknown", 0);
}
public Emp( String first, String last, int s) {
init (first, last, s);
}

public void setFirstName ( String first ) {fname = first;}
public String getFirstName() {return fname;}
public void setLastName( String last ) {lname = last;}
public String getLastName() {return lname;}
public void setSalary (int s) {sal =s;}
public int setSalary () {return sal;}
public Date getStartDate() { return start;}

public void print() {
System.out.println();
System.out.println( "First name: "+fname);
System.out.println( "Last name: "+lname);
System.out.println( "Salary:   $"+sal);
System.out.println( "Start Date: " + start);
}

private void init ( String f, String l, int s ) {
fname = f;
lname = l;
sal = s;
start = new Date ();
}
}