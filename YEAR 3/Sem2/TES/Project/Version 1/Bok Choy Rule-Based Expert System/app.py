import streamlit as st
from expert_system import BokChoyExpertSystem
from knowledge_base import SYMPTOMS, DISEASES

def main():
    st.set_page_config(
        page_title="Bok Choy Crop Disease Diagnosis",
        layout="wide",
        initial_sidebar_state="expanded"
    )

    st.markdown("""
        <style>
        .main-header {
            background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            color: white;
        }
        .symptom-badge {
            background-color: #1e3a5f;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            margin: 0.25rem 0;
            border-left: 3px solid #2196f3;
            color: white;
        }
        .info-card {
            background-color: #2d2d2d;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #444;
            color: white;
        }
        .disease-card {
            background-color: #3d2f1f;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 1rem;
            color: white;
        }
        </style>
    """, unsafe_allow_html=True)

    st.markdown("""
        <div class="main-header">
            <h1>Bok Choy Crop Disease Diagnosis Expert System</h1>
        </div>
    """, unsafe_allow_html=True)

    if 'expert_system' not in st.session_state:
        st.session_state.expert_system = BokChoyExpertSystem()

    with st.sidebar:
        st.markdown("### Symptom Selection")
        st.markdown("---")
        st.markdown("**Select all symptoms you observe:**")
        
        selected_symptoms = []

        symptom_categories = {
            "Leaf Symptoms": [
                "dark_brown_circular_spots",
                "concentric_rings",
                "yellowing_premature_drop",
                "v_shaped_yellow_lesions",
                "blackened_veins",
                "yellow_patches_upper",
                "gray_purple_fuzzy_under",
                "leaf_browning_death",
                "small_gray_white_spots",
                "angular_irregular_lesions",
                "leaf_yellowing_defoliation",
                "mosaic_patterns",
                "leaf_distortion",
                "yellowing_lower_leaves"
            ],
            "Stem & Base": [
                "water_soaked_mushy",
                "soft_decay_stem_base",
                "gray_lesions_stems",
                "black_purple_margins",
                "brown_stem_lesions",
                "seedling_collapse_soil_line",
                "bottom_leaf_rot"
            ],
            "Root Symptoms": [
                "blackened_roots_stems",
                "swollen_club_roots",
                "vascular_discoloration"
            ],
            "Growth Issues": [
                "wilting_stunted",
                "stunted_growth",
                "wilting_hot_periods",
                "sudden_collapse",
                "seedling_damping_off",
                "wilting_warm_weather"
            ],
            "Other": [
                "foul_odor"
            ]
        }

        for category, symptoms in symptom_categories.items():
            with st.expander(category, expanded=False):
                for symptom in symptoms:
                    if symptom in SYMPTOMS:
                        if st.checkbox(SYMPTOMS[symptom], key=symptom):
                            selected_symptoms.append(symptom)

        st.markdown("---")
        if selected_symptoms:
            st.success(f"{len(selected_symptoms)} symptom(s) selected")
        else:
            st.info("Select symptoms above")

    if not selected_symptoms:
        st.info("Please select symptoms from the sidebar to begin diagnosis.")
        
        st.markdown("""
            <div class="info-card">
                <h3>How to use:</h3>
                <ol>
                    <li>Observe your bok choy plants carefully</li>
                    <li>Select all applicable symptoms from the sidebar</li>
                    <li>Click the "Diagnose Disease" button</li>
                    <li>Review the diagnosis and treatment recommendations</li>
                </ol>
            </div>
        """, unsafe_allow_html=True)
    else:
        st.markdown("### Selected Symptoms")
        st.markdown(f"**Total:** {len(selected_symptoms)} symptom(s)")
        
        cols = st.columns(2)
        for idx, symptom in enumerate(selected_symptoms):
            with cols[idx % 2]:
                st.markdown(f'<div class="symptom-badge">• {SYMPTOMS[symptom]}</div>', unsafe_allow_html=True)

        st.markdown("---")
        
        # Diagnose button moved here, inside the selected symptoms section
        col1, col2, col3 = st.columns([1, 2, 1])
        with col2:
            diagnose_button = st.button("Diagnose Disease", type="primary", use_container_width=True)

        if diagnose_button:
            st.markdown("### Diagnosis")
            
            with st.spinner("Analyzing symptoms..."):
                st.session_state.expert_system.reset()
                
                for symptom in selected_symptoms:
                    st.session_state.expert_system.add_symptom(symptom, True)
                
                results = st.session_state.expert_system.diagnose()

                if results:
                    st.success(f"Found {len(results)} possible disease(s)")

                    for i, disease_name in enumerate(results, 1):
                        if disease_name in DISEASES:
                            disease_info = DISEASES[disease_name]
                            
                            st.markdown(
                                f"""
                                <div class="disease-card">
                                <h3>{i}. {disease_name}</h3>
                                </div>
                                """, 
                                unsafe_allow_html=True
                            )
                            
                            col_left, col_right = st.columns(2)
                            
                            with col_left:
                                st.markdown("**Key Symptoms:**")
                                for symptom in disease_info['symptoms']:
                                    st.markdown(f"• {symptom}")
                            
                            with col_right:
                                st.markdown("**Treatments & Control:**")
                                for j, treatment in enumerate(disease_info['treatments'], 1):
                                    st.markdown(f"{j}. {treatment}")
                            
                            st.markdown('</div>', unsafe_allow_html=True)
                            
                            if i < len(results):
                                st.markdown("<br>", unsafe_allow_html=True)
                        else:
                            st.warning(f"Information not available for: {disease_name}")
                else:
                    st.warning("""
                        **No diseases matched the selected symptoms.**
                        
                        Try:
                        - Selecting different symptoms
                        - Adding more symptoms for better accuracy
                        - Checking if symptoms are correctly identified
                    """)

    st.markdown("---")
    st.markdown("### System Information")
    
    info1, info2 = st.columns(2)
    
    with info1:
        st.markdown("""
            <div class="info-card">
                <h4>About This System</h4>
                <p>This expert system uses rule-based and forward chaining approach to diagnose bok choy diseases based on observable symptoms.</p>
                <p><strong>Tools used:</strong> Python, CLIPS</p>
            </div>
        """, unsafe_allow_html=True)
    
    with info2:
        st.markdown(f"""
            <div class="info-card">
                <h4>Knowledge Base Information</h4>
                <p><strong>{len(DISEASES)}</strong> diseases covered</p>
                <p><strong>{len(SYMPTOMS)}</strong> symptoms tracked</p>
            </div>
        """, unsafe_allow_html=True)

    st.markdown("---")
    st.markdown("### Available Diseases")
    
    disease_cols = st.columns(3)
    diseases_list = list(DISEASES.keys())
    
    for idx, disease in enumerate(diseases_list):
        with disease_cols[idx % 3]:
            st.markdown(f"• **{disease}**")

    st.markdown("---")
    st.markdown("""
        <div style="text-align: center; color: #666; padding: 1rem;">
            <p><strong>Sources:</strong> gardenerspath.com, gardeningknowhow.com, smartgardener.com</p>
            <p>© Bok Choy Disease Diagnosis Expert System</p>
        </div>
    """, unsafe_allow_html=True)

if __name__ == "__main__":
    main()