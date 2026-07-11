import javax.swing.*;
import java.io.*;
import java.util.Stack;
public class FileDataHandling implements DataHandling {
    private static final File FILE_NAME =new File ("user data.txt");
    private static final File TEMP_FILE_NAME = new File ("Temp.txt");
    
    @Override
    public void stored(String username, double height, double weight, double bmi, double targeted_bmi, double progression){
        boolean isAdded=false;
        ensureFilesExist();
        try (BufferedReader reader = new BufferedReader(new FileReader(FILE_NAME));
        BufferedWriter writer = new BufferedWriter(new FileWriter(TEMP_FILE_NAME, true))) {
            String line;
            while ((line=reader.readLine())!=null)
            {
                String[] parts = line.split(",");
                if (parts[0].trim().equals(username))
                {
                    writer.write(username + "," + height+","+weight+","+bmi+","+targeted_bmi+","+progression);
                    isAdded=true;
                }
                else
                {
                    writer.write(line);
                }
                writer.newLine();
            }
            if (!isAdded)
            {
                writer.write(username + "," + height+","+weight+","+bmi+","+targeted_bmi+","+progression);
                writer.newLine();
                isAdded=true;
            }
            JOptionPane.showMessageDialog(null, "Data addition Successful!", "Success", JOptionPane.INFORMATION_MESSAGE);
        }catch (IOException ex) {
            JOptionPane.showMessageDialog(null, "Error writing or reading to file!", "Error", JOptionPane.ERROR_MESSAGE);
        }
        if (FILE_NAME.delete()){
            if (!TEMP_FILE_NAME.renameTo(FILE_NAME)) {
                JOptionPane.showMessageDialog(null, "Error renaming temporary file!", "Error", JOptionPane.ERROR_MESSAGE);
            }
        } else {
            JOptionPane.showMessageDialog(null, "Error deleting original file!", "Error", JOptionPane.ERROR_MESSAGE);
        }
    }
    private void ensureFilesExist(){
        try{
            if (!FILE_NAME.exists()){
                FILE_NAME.createNewFile();
            }
            if (!TEMP_FILE_NAME.exists()){
                TEMP_FILE_NAME.createNewFile();
            }
        }catch (IOException ex){
                JOptionPane.showMessageDialog(null, "Error creating files!", "Error", JOptionPane.ERROR_MESSAGE);
            }
    }
    
    @Override
    public void getUsername(Stack<String> UserName)
    {
        try(BufferedReader reader=new BufferedReader(new FileReader(FILE_NAME)))
        {
            String line;
            while ((line=reader.readLine())!=null)
            {
                String[] parts = line.split(",");
                UserName.push(parts[0].trim());
            }
        }catch (IOException ex) {
            JOptionPane.showMessageDialog(null, "Error writing or reading to file!", "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    @Override
    public String getUsernameInfo(String username)
    {
        try(BufferedReader reader=new BufferedReader(new FileReader(FILE_NAME)))
        {
            String line;
            while ((line=reader.readLine())!=null)
            {
                String[] parts = line.split(",");
                if (parts[0].trim().equals(username))
                {
                    return  "Name         :" + parts[0].trim()+ "\n"+
                            "Height       :" + parts[1].trim()+ "\n"+
                            "Weight       :" + parts[2].trim()+ "\n"+
                            "BMI          :" + parts[3].trim()+ "\n"+
                            "Targeted BMI :" + parts[4].trim()+ "\n"+
                            "Progression  :" + parts[5].trim();
                }
            }
return "Username not found!";
        }catch (IOException ex) {
            JOptionPane.showMessageDialog(null, "Error writing or reading to file!", "Error", JOptionPane.ERROR_MESSAGE);
return "Error reading file!";
        }
    }
}