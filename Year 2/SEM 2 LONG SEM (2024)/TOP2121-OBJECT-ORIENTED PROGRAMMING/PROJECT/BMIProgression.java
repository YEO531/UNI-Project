import javafx.application.Platform;
import javafx.scene.Scene;
import javafx.scene.chart.LineChart;
import javafx.scene.chart.NumberAxis;
import javafx.scene.chart.XYChart;
import javafx.stage.Stage;

public class BMIProgression {
    private StackForRecord stackForRecord;
    private StackForRecord TempstackForRecord;
    private int amount;
    private double startbmi;

    public BMIProgression(StackForRecord stackForRecord, double startbmi) {
        this.stackForRecord = stackForRecord;
        this.startbmi = startbmi;
        this.TempstackForRecord = new StackForRecord();
    }

    public void displayGraph() {
        if (!Platform.isFxApplicationThread()) {
            Platform.runLater(this::displayGraph);
            return;
        }

        Stage stage = new Stage();
        amount = stackForRecord.Amount();

        double minBmi = Math.min(startbmi, (amount > 0) ? stackForRecord.MinValue() : startbmi);
        double maxBmi = Math.max(startbmi, (amount > 0) ? stackForRecord.MaxValue() : startbmi);

        // Swap the axes: Time points on X-axis, BMI on Y-axis
        NumberAxis xAxis = new NumberAxis("Time Points", 0, amount + 1, 1);
        NumberAxis yAxis = new NumberAxis("BMI Value", minBmi - 0.5, maxBmi + 0.5, 0.5);

        LineChart<Number, Number> lineChart = new LineChart<>(xAxis, yAxis);
        lineChart.setTitle("BMI Progression Over Time");

        XYChart.Series<Number, Number> series = new XYChart.Series<>();
        series.setName("BMI Changes");

        series.getData().add(new XYChart.Data<>(0, startbmi));

        TempstackForRecord = new StackForRecord();

        for (int i = 1; i <= amount; i++) {
            double val = stackForRecord.popHere();
            series.getData().add(new XYChart.Data<>(i, val)); // Swap the data points
            TempstackForRecord.pushHere(val);
        }

        for (int i = 0; i < amount; i++) {
            stackForRecord.pushHere(TempstackForRecord.popHere());
        }

        lineChart.getData().add(series);

        Scene scene = new Scene(lineChart, 800, 600);
        stage.setScene(scene);
        stage.show();
    }
}