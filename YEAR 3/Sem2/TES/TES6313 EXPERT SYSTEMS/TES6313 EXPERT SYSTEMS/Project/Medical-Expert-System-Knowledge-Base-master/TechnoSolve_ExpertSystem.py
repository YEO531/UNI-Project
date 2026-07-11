# TES6313 Project
# Lab 2C
# Group Name: TechnoSolve
# Project Title: Healthcare and Medical Diagnosis System

# Group Members:
# 1. JORDAN OON ZI SHNEG    242UT244BJ
# 2. WONG WEI XUAN          242UT244BN
# 3. TYE ZHI PING           242UT244BG

import tkinter as tk
from tkinter import messagebox
from pyknow import *

# Global variables
diseases_list = []
diseases_symptoms = []
symptom_map = {}
d_desc_map = {}
d_treatment_map = {}

# Preprocessing function to load data from files
def preprocess():
    global diseases_list, diseases_symptoms, symptom_map, d_desc_map, d_treatment_map
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

# Functions to retrieve disease details and treatments
def get_details(disease):
    return d_desc_map.get(disease, "Description not available.")

def get_treatments(disease):
    return d_treatment_map.get(disease, "Treatment not available.")

# Knowledge Engine for Disease Diagnosis
class Greetings(KnowledgeEngine):
    def __init__(self, symptom_inputs):
        super().__init__()
        self.symptom_inputs = symptom_inputs
        self.not_match = False

    @DefFacts()
    def _initial_action(self):
        yield Fact(action="find_disease")

    @Rule(Fact(action='find_disease'))
    def find_disease(self):
        for symptom, value in self.symptom_inputs.items():
            self.declare(Fact(**{symptom: value}))

    @Rule(Fact(action='find_disease'), NOT(Fact(headache=W())), salience=1)
    def symptom_0(self):
        self.declare(Fact(headache=self.symptom_inputs.get("headache", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(back_pain=W())), salience=1)
    def symptom_1(self):
        self.declare(Fact(back_pain=self.symptom_inputs.get("back_pain", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(chest_pain=W())), salience=1)
    def symptom_2(self):
        self.declare(Fact(chest_pain=self.symptom_inputs.get("chest_pain", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(cough=W())), salience=1)
    def symptom_3(self):
        self.declare(Fact(cough=self.symptom_inputs.get("cough", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(fainting=W())), salience=1)
    def symptom_4(self):
        self.declare(Fact(fainting=self.symptom_inputs.get("fainting", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(fatigue=W())), salience=1)
    def symptom_5(self):
        self.declare(Fact(fatigue=self.symptom_inputs.get("fatigue", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(low_body_temp=W())), salience=1)
    def symptom_7(self):
        self.declare(Fact(low_body_temp=self.symptom_inputs.get("low_body_temp", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(restlessness=W())), salience=1)
    def symptom_8(self):
        self.declare(Fact(restlessness=self.symptom_inputs.get("restlessness", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(sore_throat=W())), salience=1)
    def symptom_9(self):
        self.declare(Fact(sore_throat=self.symptom_inputs.get("sore_throat", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(fever=W())), salience=1)
    def symptom_10(self):
        self.declare(Fact(fever=self.symptom_inputs.get("fever", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(nausea=W())), salience=1)
    def symptom_11(self):
        self.declare(Fact(nausea=self.symptom_inputs.get("nausea", "no")))

    @Rule(Fact(action='find_disease'), NOT(Fact(blurred_vision=W())), salience=1)
    def symptom_12(self):
        self.declare(Fact(blurred_vision=self.symptom_inputs.get("blurred_vision", "no")))

    # Rule 1
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="yes"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="yes"), Fact(nausea="yes"), Fact(blurred_vision="no"))
    def disease_0(self):
        self.declare(Fact(disease="Jaundice"))

    # Rule 2
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="yes"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_1(self):
        self.declare(Fact(disease="Alzheimers"))

    # Rule 3
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="yes"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="yes"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_2(self):
        self.declare(Fact(disease="Arthritis"))

    # Rule 4
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="yes"), Fact(cough="yes"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="yes"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_3(self):
        self.declare(Fact(disease="Tuberculosis"))

    # Rule 5
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="yes"), Fact(cough="yes"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="yes"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_4(self):
        self.declare(Fact(disease="Asthma"))

    # Rule 6
    @Rule(Fact(action='find_disease'), Fact(headache="yes"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="yes"), Fact(fainting="no"), Fact(sore_throat="yes"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="yes"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_5(self):
        self.declare(Fact(disease="Sinusitis"))

    # Rule 7
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="yes"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_6(self):
        self.declare(Fact(disease="Epilepsy"))

    # Rule 8
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="yes"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="yes"), Fact(blurred_vision="no"))
    def disease_7(self):
        self.declare(Fact(disease="Heart Disease"))

    # Rule 9
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="yes"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="yes"), Fact(blurred_vision="yes"))
    def disease_8(self):
        self.declare(Fact(disease="Diabetes"))

    # Rule 10
    @Rule(Fact(action='find_disease'), Fact(headache="yes"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="yes"), Fact(blurred_vision="yes"))
    def disease_9(self):
        self.declare(Fact(disease="Glaucoma"))

    # Rule 11
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="yes"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="no"), Fact(nausea="yes"), Fact(blurred_vision="no"))
    def disease_10(self):
        self.declare(Fact(disease="Hyperthyroidism"))

    # Rule 12
    @Rule(Fact(action='find_disease'), Fact(headache="yes"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="no"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="no"), Fact(fever="yes"), Fact(nausea="yes"), Fact(blurred_vision="no"))
    def disease_11(self):
        self.declare(Fact(disease="Heat Stroke"))

    # Rule 13
    @Rule(Fact(action='find_disease'), Fact(headache="no"), Fact(back_pain="no"), Fact(chest_pain="no"), Fact(cough="no"), Fact(fainting="yes"), Fact(sore_throat="no"), Fact(fatigue="no"), Fact(restlessness="no"), Fact(low_body_temp="yes"), Fact(fever="no"), Fact(nausea="no"), Fact(blurred_vision="no"))
    def disease_12(self):
        self.declare(Fact(disease="Hypothermia"))

    # Rule 14
    @Rule(Fact(action='find_disease'),
          Fact(headache=MATCH.headache),
          Fact(back_pain=MATCH.back_pain),
          Fact(chest_pain=MATCH.chest_pain),
          Fact(cough=MATCH.cough),
          Fact(fainting=MATCH.fainting),
          Fact(sore_throat=MATCH.sore_throat),
          Fact(fatigue=MATCH.fatigue),
          Fact(low_body_temp=MATCH.low_body_temp),
          Fact(restlessness=MATCH.restlessness),
          Fact(fever=MATCH.fever),
          Fact(nausea=MATCH.nausea),
          Fact(blurred_vision=MATCH.blurred_vision), NOT(Fact(disease=MATCH.disease)), salience=-999)
    def not_matched(self, headache, back_pain, chest_pain, cough, fainting, sore_throat, fatigue, restlessness, low_body_temp, fever, nausea, blurred_vision):
        lis = [headache, back_pain, chest_pain, cough, fainting, sore_throat, fatigue, restlessness, low_body_temp, fever, nausea, blurred_vision]
        max_count = 0
        max_disease = ""
        self.not_match = True
        for key, val in symptom_map.items():
            count = 0
            temp_list = eval(key)
            for j in range(len(lis)):
                if temp_list[j] == lis[j] and lis[j] == "yes":
                    count += 1
            if count > max_count:
                max_count = count
                max_disease = val
        if max_disease:
            self.declare(Fact(disease=max_disease))
        else:
            self.result = "No matching disease found."

    # show result
    @Rule(Fact(action='find_disease'), Fact(disease=MATCH.disease), salience=-998)
    def disease(self, disease):
        disease_details = get_details(disease)
        treatments = get_treatments(disease)
        if self.not_match:
            self.result = f"Not matching disease found!!!\n\nThe most probable disease is {disease}.\n\nDescription:\n{disease_details}\n\nTreatment:\n{treatments}"
        
        else:
            self.result = f"The most probable disease is {disease}.\n\nDescription:\n{disease_details}\n\nTreatment:\n{treatments}"

    def run_engine(self):
        self.result = "No matching disease found."
        self.reset()
        self.run()
        return self.result

# GUI Implementation
def run_gui():
    preprocess()

    def diagnose():
        symptoms = {symptom: var.get() for symptom, var in symptom_vars.items()}
        engine = Greetings(symptoms)
        result = engine.run_engine()
        messagebox.showinfo("Diagnosis Result", result)
        # if not_match:
        #     messagebox.showinfo("Diagnosis Result:\n\nNot matching disease found!!!\n\n", result)
        
        # else:
        #     messagebox.showinfo("Diagnosis Result", result)

    def reset_choices():
        for var in symptom_vars.values():
            var.set("no")

    root = tk.Tk()
    root.title("Medical Diagnosis System")

    tk.Label(root, text="Select Symptoms", font=("Arial", 16)).pack(pady=10)

    symptom_vars = {}
    for symptom in ["headache", "back_pain", "chest_pain", "cough", "fainting", "sore_throat", "fatigue", "restlessness", "low_body_temp", "fever", "nausea", "blurred_vision"]:
        var = tk.StringVar(value="no")
        symptom_vars[symptom] = var

        frame = tk.Frame(root)
        frame.pack(anchor="w", padx=20, pady=2)

        tk.Label(frame, text=symptom.replace("_", " ").capitalize(), font=("Helvetica", 12), width=20, anchor="w").pack(side="left")
        tk.Radiobutton(frame, text="Yes", variable=var, value="yes", font=("Helvetica", 12)).pack(side="left")
        tk.Radiobutton(frame, text="No", variable=var, value="no", font=("Helvetica", 12)).pack(side="left")

    tk.Button(root, text="Reset Value", command=reset_choices, font=("Arial", 14), bg="blue", fg="white").pack(pady=10)
    tk.Button(root, text="Diagnose", command=diagnose, font=("Arial", 14), bg="green", fg="white").pack(pady=20)
    tk.Button(root, text="Exit", command=root.quit, font=("Arial", 14), bg="red", fg="white").pack(pady=10)

    root.mainloop()

if __name__ == "__main__":
    run_gui()