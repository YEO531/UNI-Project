import java.util.Stack;
public interface DataHandling {
    void stored(String username, double height, double weight, double bmi, double targeted_bmi, double progression);
    void getUsername(Stack<String> UserName);
    String getUsernameInfo(String username);
}