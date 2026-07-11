import java.applet.*;
import java.awt.*;
public class MyFriends extends Applet
{
Person p1 = new Person ("Timothy");
American p2 = new American ("John");
Spaniard p3 = new Spaniard ("Booch");
Malaysian p4 = new Malaysian ("Muhamed");
public void paint (Graphics g)
{
p1.draw(g);
p2.draw(g);
p3.draw(g);
p4.draw(g);
}
}