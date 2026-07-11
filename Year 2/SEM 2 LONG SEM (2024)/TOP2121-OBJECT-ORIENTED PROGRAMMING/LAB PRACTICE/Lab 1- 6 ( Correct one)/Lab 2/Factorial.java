import javax.swing.*;
public class Factorial{
  public static void main(String a[]){
     String s="";
     int k;
     JTextArea out = new JTextArea(7,10);
     for(int i=0; i<10; i++){
         k=fact(i);
         s=s+i+"\t"+k+"\n";
     }
     out.setText(s);
     JOptionPane.showMessageDialog(null,out,
          "My Output", JOptionPane.INFORMATION_MESSAGE);
     System.exit(0);
  }

  static int fact(int i){
    if((i==0)||(i==1)) return 1;
    else return i*fact (i-1);
  }
}
