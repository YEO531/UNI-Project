import java.util.*; 
class TokenExample{   
  public static void main(String args[]){     
     String s = "This%is!the$way#the@world%ends";      
     String delimiters = "!%$#@";
     StringTokenizer st = new StringTokenizer(s,delimiters);
     while(st.hasMoreTokens())
       System.out.println(st.nextToken() );
  }
}
