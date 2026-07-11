public interface Authenticator {
    boolean authenticate(String username, String password);
    void register(String username, String password);
}