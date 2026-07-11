import java.util.*; 
class VectorExample{   
     public static void main(String args[]){      
          int n;     
          Vector<String> v = new Vector<String>();     
          v.addElement("aristotle");      
          v.addElement("descartes");      
          v.addElement("euclid");      
          v.addElement("newton");
          v.addElement("einstein");
          v.addElement("new");

          n=v.size();
          System.out.println("Total number of elements = " + n);
          for(int i=0; i<n; i++)
          System.out.println(v.elementAt(i) );
     }
}