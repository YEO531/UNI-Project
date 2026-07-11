public class Rectangle
{
double width;
double length;

public Rectangle( double h, double w)
{
width = w;
length = h;
}
double getArea( )
{
return length*width;
}
double getPerimeter( )
{
return 2*(length*width);
}

public void print()
{
System.out.println("The Area is: " + getArea() );
System.out.println("The Perimeter is: " + getPerimeter() );
}
}