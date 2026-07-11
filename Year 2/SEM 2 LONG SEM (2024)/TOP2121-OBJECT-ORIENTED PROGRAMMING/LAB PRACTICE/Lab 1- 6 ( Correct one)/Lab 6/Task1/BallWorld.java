   import java.awt.*;
   import java.applet.*;
   public class BallWorld extends Applet{
      public static int left=50, right=350, top=50, bottom=350;
      Ball b1=new Ball (Color.red,100,200,80,10,15);
      Ball b2=new Ball (Color.green,200,100,20,15,10);
      Ball b3=new Ball (Color.magenta,250,150,15,6,10);
      Ball b4=new Ball (Color.cyan,250,300,25,-6,-10);

      public void paint(Graphics g)
      {
        g.setColor(Color.blue);
        g.drawRect(left,top,right-left,bottom-top);
        b1.draw(g);  //draw ball 1
        b2.draw(g);  //draw ball 2
        b3.draw(g);  //draw ball 3
        b4.draw(g);  //draw ball 4
        slow(100);  //slow the display
        repaint();
      }
      public void slow(int t){
        try{
          Thread.sleep(t);
        }catch(Exception e){}
      }
   }
