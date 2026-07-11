public class DietPlan {
    private final String name;
    private final String description;
    private final String[] meals;

    public DietPlan(String name, String description, String[] meals) {
        this.name = name;
        this.description = description;
        this.meals = meals;
    }

    public String getName() { return name; }
    public String getDescription() { return description; }
    public String[] getMeals() { return meals; }
}
