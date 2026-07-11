import java.util.Random;

public class AlphabetCount {
    private char[] alphabet;
    private int[] counts;

    public AlphabetCount() {
        alphabet = new char[100];
        counts = new int[26];
    }

    public void generateAlphabet() {
        Random random = new Random();
        for (int i = 0; i < alphabet.length; i++) {
            alphabet[i] = (char)(random.nextInt(26) + 'A');
        }
    }

    public void countAlphabet() {
        for (int i = 0; i < counts.length; i++) {
            counts[i] = 0;
        }
        for (char letter : alphabet) {
            counts[letter - 'A']++;
        }
    }

    public void displayResult() {
        System.out.println("The occurrences of each alphabet letter are:");
        for (int i = 0; i < counts.length; i++) {
            System.out.printf("%-3s: %-2d  ", (char)('A' + i), counts[i]);
            if ((i + 1) % 13 == 0) {
                System.out.println();
            }
        }
        if (counts.length % 15 != 0) {
            System.out.println();
        }
    }
}
