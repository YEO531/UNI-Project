
import java.util.Scanner;

	

public class Calendar {
    
    Scanner keyboard;
    String months[];
    int currentYear;
    int currentMonth;
    String username;
    
    public Calendar() {
        keyboard = new Scanner(System.in);
        
        months = new String[] {
            "January", "February", "March", "April", "May", "Jun",
            "July", "August", "September", "October", "November", "December"
        };
    }

private void getUsername(){
	System.out.print("Please enter name: ");
        username = keyboard.nextLine();
        System.out.println("\n  " + "Welcome, " + username );
	System.out.println("\n");
    }
       
    public void runProgram() {
	System.out.println("\nCalendar Program");
        System.out.println("----------------------");
        
	getUsername();
	
        getInput();
        
        makeCalendar();
        
        keyboard.close();
    }

    
    private void getInput() {
        do {
            System.out.print("Enter year : ");
            currentYear = keyboard.nextInt();
            System.out.print("Enter month (1-12): ");
            currentMonth = keyboard.nextInt();
            
            if(currentMonth < 1 || currentMonth > 12) {
                System.out.println("Month must be 1-12!");
            }
        } while(currentMonth < 1 || currentMonth > 12);
    }
    
    private void makeCalendar() {
        System.out.println("\n     " + months[currentMonth-1] + " " + currentYear);
        System.out.println("----------------------");
        System.out.println("Su Mo Tu We Th Fr Sa");  
        
        int startDay = findStartDay();
        int daysInMonth = findDaysInMonth();
        
        
        String space = "   ";  
        for(int i = 0; i < startDay; i++) {
            System.out.print(space);
        }
        
        
        int currentPos = startDay;
        for(int day = 1; day <= daysInMonth; day++) {
            
            if(day < 10) {
                System.out.print(" ");
            }
            
            System.out.print(day + " ");
            currentPos++;
            
            
            if(currentPos % 7 == 0) {
                System.out.println();
            }
        }
        System.out.println("\n");  
    }
    
    
    private int findStartDay() {
        int m = currentMonth;
        int y = currentYear;
        
        
        if(m < 3) {
            m += 12;
            y--;
        }
        
        
        int k = y % 100;
        int j = y / 100;
        int h = 1 + ((13*(m+1))/5) + k + (k/4) + (j/4) - (2*j);
        
        return ((h + 5) % 7);
    }
    
    private int findDaysInMonth() {
        if(currentMonth == 4 || currentMonth == 6 || 
           currentMonth == 9 || currentMonth == 11) {
            return 30;
        }
        
        if(currentMonth == 2) {
            if(isLeapYear()) {
                return 29;
            } else {
                return 28;
            }
        }
        
        return 31;
    }
    
        private boolean isLeapYear() {
        if(currentYear % 4 != 0) {
            return false;
        }
        if(currentYear % 100 != 0) {
            return true;
        }
        return (currentYear % 400 == 0);
    }
    
    public static void main(String[] args) {
        Calendar cal = new Calendar();
        cal.runProgram();
    }
}