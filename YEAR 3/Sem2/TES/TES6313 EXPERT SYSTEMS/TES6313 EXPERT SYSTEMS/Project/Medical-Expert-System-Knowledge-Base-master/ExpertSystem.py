import tkinter as tk
from tkinter import messagebox, Toplevel

diseases_list = []
diseases_symptoms = []
symptom_map = {}
d_desc_map = {}
d_treatment_map = {}

def preprocess():
	global diseases_list,diseases_symptoms,symptom_map,d_desc_map,d_treatment_map
	diseases = open("diseases.txt")
	diseases_t = diseases.read()
	diseases_list = diseases_t.split("\n")
	diseases.close()
	for disease in diseases_list:
		disease_s_file = open("New symptoms/" + disease + ".txt", encoding='utf-8')
		disease_s_data = disease_s_file.read()
		s_list = disease_s_data.split("\n")
		diseases_symptoms.append(s_list)
		symptom_map[str(s_list)] = disease
		disease_s_file.close()
		disease_s_file = open("Disease descriptions/" + disease + ".txt", encoding='utf-8')
		disease_s_data = disease_s_file.read()
		d_desc_map[disease] = disease_s_data
		disease_s_file.close()
		disease_s_file = open("Disease treatments/" + disease + ".txt", encoding='utf-8')
		disease_s_data = disease_s_file.read()
		d_treatment_map[disease] = disease_s_data
		disease_s_file.close()
          
def identify_disease(*arguments):
	symptom_list = []
	for symptom in arguments:
		symptom_list.append(symptom)
	# Handle key error
	return symptom_map[str(symptom_list)]

def get_details(disease):
	return d_desc_map[disease]

def get_treatments(disease):
	return d_treatment_map[disease]

def if_not_matched(disease):
    print("")
    id_disease = disease
    disease_details = get_details(id_disease)
    treatments = get_treatments(id_disease)
    print("")
    print("The most probable disease that you have is %s\n" %(id_disease))
    print("A short description of the disease is given below :\n")
    print(disease_details+"\n")
    print("The common medications and procedures suggested by other real doctors are: \n")
    print(treatments+"\n")
    return id_disease

class ExpertSystem:
    def __init__(self, root):
        self.root = root
        self.root.title("Dr. Yar - Expert System")
        self.root.geometry("1000x700")

        # Initialize frames
        self.main_frame = tk.Frame(root)
        self.symptom_frame = tk.Frame(root)
        self.result_frame = tk.Frame(root)

        # Run the initial main frame
        self.create_main_frame()

    def create_main_frame(self):
        # Clear the root window and add main frame widgets
        self.clear_frame()
        tk.Label(self.main_frame, text="Hi! I am Dr. Yar,", font=("Helvetica", 20, "bold")).pack(pady=30)
        tk.Label(self.main_frame, text="I am here to help you make your health better.\n\n\n",
                 font=("Helvetica", 20)).pack(pady=20)
        tk.Button(self.main_frame, text="Start", font=("Helvetica", 20, "bold"), command=self.create_symptom_frame, bg="green", width=18, height=3).pack(pady=10)
        tk.Button(self.main_frame, text="End", font=("Helvetica", 20, "bold"), command=self.root.destroy, bg="red", width=18, height=3).pack(pady=10)
        self.main_frame.pack()

    def create_symptom_frame(self):
        # Clear the root window and add symptom frame widgets
        self.clear_frame()
        self.symptoms = {}  # To store user responses
        symptoms = [
            "Headache",
            "Back pain",
            "Chest pain",
            "Cough",
            "Fainting",
            "Fatigue",
            "Low body temperature",
            "Restlessness",
            "Sore throat",
            "Fever",
            "Nausea",
            "Blurred vision",
        ]

        tk.Label(self.symptom_frame, text="Answer the following questions:", font=("Helvetica", 20, "bold")).pack(pady=10)

        for symptom in symptoms:
            frame = tk.Frame(self.symptom_frame)
            tk.Label(frame, text=symptom, font=("Helvetica", 12), anchor="w").pack(side=tk.LEFT, padx=(0, 20))
            response = tk.StringVar(value="no")
            self.symptoms[symptom] = response
            tk.Radiobutton(frame, text="No", variable=response, value="no", font=("Helvetica", 12)).pack(side=tk.RIGHT, padx=10)
            tk.Radiobutton(frame, text="Yes", variable=response, value="yes", font=("Helvetica", 12)).pack(side=tk.RIGHT, padx=10)
            frame.pack(anchor="w", pady=5, fill="x")

        tk.Button(self.symptom_frame, text="Submit", font=("Helvetica", 12), bg="blue", fg="white",
                  command=self.display_result).pack(pady=10)
        self.symptom_frame.pack()

    def display_result(self):
        # Analyze symptoms to determine the probable disease
        headache = self.symptoms["Headache"].get()
        back_pain = self.symptoms["Back pain"].get()
        chest_pain = self.symptoms["Chest pain"].get()
        cough = self.symptoms["Cough"].get()
        fainting = self.symptoms["Fainting"].get()
        fatigue = self.symptoms["Fatigue"].get()
        low_body_temperature = self.symptoms["Low body temperature"].get()
        restlessness = self.symptoms["Restlessness"].get()
        sore_throat = self.symptoms["Sore throat"].get()
        fever = self.symptoms["Fever"].get()
        nausea = self.symptoms["Nausea"].get()
        blurred_vision = self.symptoms["Blurred vision"].get()
        not_find = False

        # Rule
        # rule 1
        if (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "yes" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "yes" and nausea == "yes" and blurred_vision == "no"):
            probable_disease = "Jaundice"

        # rule 2
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "yes" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Alzheimers"

        # rule 3
        elif (headache == "no" and back_pain == "yes" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "yes" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Arthritis"

        # rule 4
        elif (headache == "no" and back_pain == "no" and chest_pain == "yes" and cough == "yes" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "yes" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Tuberculosis"

        # rule 5
        elif (headache == "no" and back_pain == "no" and chest_pain == "yes" and cough == "yes" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "yes" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Asthma"

        # rule 6
        elif (headache == "yes" and back_pain == "no" and chest_pain == "no" and cough == "yes" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "yes" and 
            fever == "yes" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Sinusitis"

        # rule 7
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "yes" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Epilepsy"

        # rule 8
        elif (headache == "no" and back_pain == "no" and chest_pain == "yes" and cough == "no" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "yes" and blurred_vision == "no"):
            probable_disease = "Heart Disease"

        # rule 9
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "yes" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "yes" and blurred_vision == "yes"):
            probable_disease = "Diabetes"

        # rule 10
        elif (headache == "yes" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "yes" and blurred_vision == "yes"):
            probable_disease = "Glaucoma"

        # rule 11
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "yes" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "yes" and blurred_vision == "no"):
            probable_disease = "Hyperthyroidism"

        # rule 12
        elif (headache == "yes" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "yes" and nausea == "yes" and blurred_vision == "no"):
            probable_disease = "Heat Stroke"

        # rule 13
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "yes" and fatigue == "no" and 
            low_body_temperature == "yes" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Hypothermia"

        # rule 14
        elif (headache == "no" and back_pain == "no" and chest_pain == "no" and cough == "no" and 
            fainting == "no" and fatigue == "no" and 
            low_body_temperature == "no" and restlessness == "no" and sore_throat == "no" and 
            fever == "no" and nausea == "no" and blurred_vision == "no"):
            probable_disease = "Unknown condition"

        # rule 15
        else:
            # print("\nDid not find any disease that matches your exact symptoms")
            not_find = True
            lis = [headache, back_pain, chest_pain, cough, fainting, sore_throat, fatigue, restlessness,low_body_temperature ,fever, nausea ,blurred_vision]
            max_count = 0
            max_disease = ""
            for key,val in symptom_map.items():
                count = 0
                temp_list = eval(key)
                for j in range(0,len(lis)):
                    if(temp_list[j] == lis[j] and lis[j] == "yes"):
                        count = count + 1
                if count > max_count:
                    max_count = count
                    max_disease = val
            probable_disease = if_not_matched(max_disease)

        self.clear_frame()
        canvas = tk.Canvas(self.result_frame, width=900, height=700)
        scrollbar = tk.Scrollbar(self.result_frame, orient="vertical", command=canvas.yview)
        scrollable_frame = tk.Frame(canvas)
    
        scrollable_frame.bind(
            "<Configure>",
            lambda e: canvas.configure(scrollregion=canvas.bbox("all"))
        )
        canvas.create_window((0, 0), window=scrollable_frame, anchor="nw")
        canvas.configure(yscrollcommand=scrollbar.set)

        def _on_mousewheel(event):
            canvas.yview_scroll(-1 * (event.delta // 120), "units")

        # Bind the event to the root window (global binding)
        self.root.bind("<MouseWheel>", _on_mousewheel)  # For Windows and macOS
        self.root.bind("<Button-4>", lambda e: canvas.yview_scroll(-1, "units"))  # For Linux scroll up
        self.root.bind("<Button-5>", lambda e: canvas.yview_scroll(1, "units"))   # For Linux scroll down

        # Pack canvas and scrollbar
        canvas.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")
        self.result_frame.pack(fill="both", expand=True)
    
        if probable_disease == "Unknown condition":
            message = (
                "We couldn't identify a specific condition based on your symptoms.\n"
                "Please consult a healthcare professional for a thorough diagnosis."
            )
            tk.Label(scrollable_frame, text=message, font=("Helvetica", 16, "bold"), fg="red", wraplength=850, justify="center").pack(pady=20)
        else:
            disease_details = get_details(probable_disease)
            treatments = get_treatments(probable_disease)

            if not_find:
                text_display = "Did not find any disease that matches your exact symptoms!!!\n\n"
                text_display += f"Probable Disease: {probable_disease}"

            else:
                text_display = f"The most probable disease that you have is {probable_disease}"
    
            tk.Label(scrollable_frame, text=text_display,
                     font=("Helvetica", 18, "bold"), fg="blue", anchor="w", wraplength=850).pack(pady=10, fill="x")
            tk.Label(scrollable_frame, text="Description:", font=("Helvetica", 16, "bold"), anchor="w").pack(anchor="w", pady=10)
            tk.Label(scrollable_frame, text=disease_details, font=("Helvetica", 14), anchor="w", wraplength=850, justify="left").pack(pady=5)
            tk.Label(scrollable_frame, text="\nSuggested Treatments:", font=("Helvetica", 16, "bold"), anchor="w").pack(anchor="w", pady=10)
            tk.Label(scrollable_frame, text=treatments, font=("Helvetica", 14), anchor="w", wraplength=850, justify="left").pack(pady=5)
    
        tk.Button(scrollable_frame, text="Back to Main Menu", font=("Helvetica", 12), bg="green", fg="white",
                  command=self.create_main_frame).pack(pady=20)
    
        canvas.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")
        self.result_frame.pack(fill="both", expand=True)
            # tk.Label(self.result_frame, text=details, font=("Helvetica", 14), wraplength=350).pack(pady=20)
            # tk.Button(self.result_frame, text="OK", font=("Helvetica", 12), command=self.create_main_frame).pack(pady=10)
            
            # # # Create a new window to display the medication information
            # # medication_window = Toplevel()
            # # medication_window.title("Medications and Procedures")
            # # medication_window.geometry("1000x700")
            # # medication_window.pack_propagate(False)

            # # treatments = get_treatments(probable_disease)

            # # # Add a Label to the new window
            # # tk.Label(medication_window, text="The common medications and procedures suggested by other real doctors are:", font=("Helvetica", 14)).pack(pady=10)
            # # tk.Label(medication_window, text=treatments, font=("Helvetica", 12), wraplength=450).pack(pady=20)
            
            # self.result_frame.pack()

    def clear_frame(self):
        # Remove all widgets from the current frame and reset its children
        for frame in [self.main_frame, self.symptom_frame, self.result_frame]:
            for widget in frame.winfo_children():
                widget.destroy()
            frame.pack_forget()



if __name__ == "__main__":
    preprocess()
    root = tk.Tk()
    app = ExpertSystem(root)
    root.mainloop()
