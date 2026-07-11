import java.util.Stack;

public class StackForRecord {
    private static int counter = 0;
    private Stack<Double> stack = new Stack<>();
    private Stack<Double> maxStack = new Stack<>();
    private Stack<Double> minStack = new Stack<>();

    public void pushHere(double BMI) {
        stack.push(BMI);
        counter++;

        if (maxStack.isEmpty() || BMI >= maxStack.peek()) {
            maxStack.push(BMI);
        } else {
            maxStack.push(maxStack.peek());
        }

        if (minStack.isEmpty() || BMI <= minStack.peek()) {
            minStack.push(BMI);
        } else {
            minStack.push(minStack.peek());
        }
    }

    public int Amount() {
        return counter;
    }

    public double MaxValue() {
        return maxStack.peek();
    }

    public double MinValue() {
        return minStack.peek(); 
    }

    public double popHere() {
        double temp = stack.pop();
        maxStack.pop();  
        minStack.pop();  
        counter--;
        return temp;
    }
}
