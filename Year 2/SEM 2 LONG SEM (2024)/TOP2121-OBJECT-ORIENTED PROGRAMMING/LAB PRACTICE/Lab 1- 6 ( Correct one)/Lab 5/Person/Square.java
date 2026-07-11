public class Square extends Rectangle
{
public Square (double h, double w)
{
super(h,w);
}
void printError()
{
System.out.println("not a square");
}
double getArea( )
{
return length*width;
}
double getPerimeter( )
{
return 2*(length+width);
}
}