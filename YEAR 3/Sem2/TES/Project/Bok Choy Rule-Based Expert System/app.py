import streamlit as st
import nltk
from nltk.tokenize import word_tokenize
from nltk.stem import WordNetLemmatizer
from nltk.corpus import stopwords
from expert_system import BokChoyAndBananaExpertSystem
from knowledge_base import SYMPTOMS, DISEASES, CF_INTERPRETATION
import re

def extract_symptoms_manually(user_text, symptom_dict, interpretation_dict):
    lemmatizer = WordNetLemmatizer()
    stop_words = set(stopwords.words('english'))
    detected = {}
    clean_text = user_text.lower().strip()
    
    segments = re.split(r'[,.\n]| and ', clean_text)
    certainty_words = sorted(interpretation_dict.keys(), key=len, reverse=True)

    for segment in segments:
        segment = segment.strip()
        if not segment: continue
        
        current_certainty = "yes"
        for word in certainty_words:
            if re.search(r'\b' + re.escape(word) + r'\b', segment):
                current_certainty = word
                segment = segment.replace(word, "").strip()
                break

        # Tokenize & Lemmatize User Input
        user_tokens = word_tokenize(segment)
        user_lemmas = {lemmatizer.lemmatize(w) for w in user_tokens if w.isalnum() and w not in stop_words}

        for key, description in symptom_dict.items():
            desc_tokens = word_tokenize(description.lower())
            desc_lemmas = {lemmatizer.lemmatize(w) for w in desc_tokens if w.isalnum() and w not in stop_words}
            
            if desc_lemmas:
                overlap = user_lemmas.intersection(desc_lemmas)
                # Calculate how much of the KNOWLEDGE BASE requirement is met
                match_ratio = len(overlap) / len(desc_lemmas)
                
                if len(desc_lemmas) > 0 and (match_ratio >= 0.6):
                    detected[key] = current_certainty
                
    return detected

def main():
    st.set_page_config(page_title="Crop Disease Expert System", layout="wide")

    # Initialize the Expert System in session state
    if 'expert_system' not in st.session_state:
        st.session_state.expert_system = BokChoyAndBananaExpertSystem()

    st.title("Bok Choy & Banana Crop Disease Diagnosis Expert System")
    st.markdown("---")

    # --- Crop Type Selection ---
    st.markdown("### Select Crop Type")
    plant_type = st.selectbox("Which crop are you diagnosing?", ["Bok Choy", "Banana"])
    st.markdown("---")

    # --- User Input Section ---
    st.markdown("### Describe the Crop Condition")
    user_description = st.text_area(
        "Enter symptoms here:", 
        placeholder="e.g., The leaves have maybe dark brown circular spots and probably concentric rings...",
        height=150
    )

    detected_map = {}
    if user_description:
        # Extract symptoms and their associated certainty levels
        detected_map = extract_symptoms_manually(user_description, SYMPTOMS, CF_INTERPRETATION)
        
        if detected_map:
            st.success(f"Detected {len(detected_map)} symptoms from your text.")
            for key, cert in detected_map.items():
                st.markdown(f"**Detected:** {SYMPTOMS[key]} (Certainty: *{cert}*)")
        else:
            st.info("No specific symptoms recognized yet.")

    st.markdown("---")

    # --- Diagnosis Logic ---
    if st.button("Run Diagnosis", type="primary"):
        if not detected_map:
            st.warning("Please describe the symptoms first.")
        else:
            st.session_state.expert_system.reset(plant_type)
            
            # Assert each symptom with its specific certainty keyword
            for symptom_key, cert_keyword in detected_map.items():
                st.session_state.expert_system.add_symptom(symptom_key, cert_keyword, plant_type)
            
            results = st.session_state.expert_system.diagnose()

            if results:
                st.markdown("### Diagnosis Results")
                for disease_name, score in results.items():
                    certainty_pct = f"{score * 100:.1f}%"
                    
                    st.success(f"**{disease_name}** identified with **{certainty_pct}** certainty.")
        
                    # Fetch and display detailed info from the Knowledge Base
                    if disease_name in DISEASES:
                        info = DISEASES[disease_name]
                        
                        col_left, col_right = st.columns(2)
                        
                        with col_left:
                            st.markdown("**Key Symptoms for this Disease:**")
                            for sym_key in info['symptoms']:
                                st.markdown(f"• {SYMPTOMS.get(sym_key, sym_key)}")
                        
                        with col_right:
                            st.markdown("**Recommended Treatments:**")
                            for treatment in info['treatments']:
                                st.markdown(f"• {treatment}")
                        st.markdown("---")
            else:
                st.error("The symptoms provided do not match any known diseases for the selected crop.")

if __name__ == "__main__":
    main()