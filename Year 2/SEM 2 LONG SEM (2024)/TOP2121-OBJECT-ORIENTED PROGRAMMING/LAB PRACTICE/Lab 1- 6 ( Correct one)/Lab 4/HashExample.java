import java.util.*; 
import javax.swing.*; 
class HashExample
{   
  public static void main(String args[]){      
    String skey;
     String[] keys ={"1991234","1211108262", "1211108837"};
     String[] names ={"Liu", "Bai", "Mo" };

     Hashtable<Object, Object> ht = new Hashtable<Object, Object>();
     for(int i=0; i<keys.length; i++)
     
      ht.put(keys[i],names[i]);
      skey=JOptionPane.showInputDialog("Enter Search Key");  
      if(ht.get(skey)==null) 
      JOptionPane.showMessageDialog(null, "There is no student with this ID");
      else   
      JOptionPane.showMessageDialog(null,"The student's name is " +ht.get(skey));      
      System.exit(0);   
      } 
}
