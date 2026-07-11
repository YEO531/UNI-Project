public class DietRecommendations {
    public static DietPlan[] getRecommendationsForBMI(double bmi) {
        if (bmi < 18.5) {  // Underweight
            return new DietPlan[] {
                new DietPlan("High-Calorie Balanced Diet",
                    "Focus on nutrient-dense foods with healthy fats",
                    new String[]{"Breakfast: Oatmeal with nuts and fruits", "Lunch: Rice with lean protein", "Dinner: Whole grain pasta with sauce"}),
                new DietPlan("Protein-Rich Diet",
                    "Emphasis on muscle building and weight gain",
                    new String[]{"Breakfast: Protein smoothie", "Lunch: Chicken with quinoa", "Dinner: Fish with sweet potato"}),
                new DietPlan("Carb-Loading Diet",
                    "Complex carbohydrates with protein for weight gain",
                    new String[]{"Breakfast: Whole grain toast with eggs", "Lunch: Brown rice bowl", "Dinner: Potato and meat stew"})
            };
        } else if (bmi < 24.9) {  // Normal
            return new DietPlan[] {
                new DietPlan("Mediterranean Diet",
                    "Heart-healthy fats and whole foods",
                    new String[]{"Breakfast: Greek yogurt with honey", "Lunch: Mediterranean salad", "Dinner: Grilled fish with vegetables"}),
                new DietPlan("Balanced Macros",
                    "Equal distribution of proteins, carbs, and fats",
                    new String[]{"Breakfast: Eggs with toast", "Lunch: Mixed grain bowl", "Dinner: Lean meat with vegetables"}),
                new DietPlan("Flexible Dieting",
                    "80/20 approach to healthy eating",
                    new String[]{"Breakfast: Fruit smoothie", "Lunch: Turkey sandwich", "Dinner: Stir-fry with rice"})
            };
        } else if (bmi < 29.9) {  // Overweight
            return new DietPlan[] {
                new DietPlan("Low-Carb Diet",
                    "Reduced carbohydrates with lean proteins",
                    new String[]{"Breakfast: Eggs and vegetables", "Lunch: Chicken salad", "Dinner: Fish with greens"}),
                new DietPlan("Portion Control Diet",
                    "Focus on portion sizes and balanced nutrition",
                    new String[]{"Breakfast: Small portion oatmeal", "Lunch: Half plate vegetables", "Dinner: Measured protein serving"}),
                new DietPlan("High-Protein Low-Calorie",
                    "Emphasis on protein for satiety",
                    new String[]{"Breakfast: Protein shake", "Lunch: Tuna salad", "Dinner: Lean meat with vegetables"})
            };
        } else {  // Obese
            return new DietPlan[] {
                new DietPlan("Calorie-Deficit Diet",
                    "Structured meal plan with reduced calories",
                    new String[]{"Breakfast: High-fiber cereal", "Lunch: Vegetable soup", "Dinner: Grilled chicken breast"}),
                new DietPlan("Low-GI Diet",
                    "Focus on foods with low glycemic index",
                    new String[]{"Breakfast: Steel-cut oats", "Lunch: Lentil soup", "Dinner: Quinoa with vegetables"}),
                new DietPlan("Mediterranean-Style",
                    "Plant-based with lean proteins",
                    new String[]{"Breakfast: Whole grain toast", "Lunch: Greek salad", "Dinner: Fish with olive oil"})
            };
        }
    }
}