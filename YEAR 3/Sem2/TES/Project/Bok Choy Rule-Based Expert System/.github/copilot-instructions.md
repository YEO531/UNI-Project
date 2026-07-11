# Bok Choy & Banana Rule-Based Expert System - AI Agent Instructions

## Project Overview
This is a **Streamlit-based expert system** that diagnoses crop diseases in Bok Choy and Banana plants using:
- **CLIPS** (Cognitive Lightweight Integrated Production System) for inference engine and rule-based reasoning
- **NLTK** for natural language processing to extract symptoms from user text
- **Certainty Factors (CF)** to model confidence levels in diagnoses

The system flows: User description → NLP extraction → Symptom detection → CLIPS inference → Disease diagnosis with certainty scores.

## Architecture & Data Flow

### Core Components
1. **[app.py](app.py)**: Streamlit UI - handles user input, symptom extraction, and result display
2. **[expert_system.py](expert_system.py)**: CLIPS environment manager - loads rules, asserts facts, runs inference, aggregates disease certainty
3. **[rules.py](rules.py)**: CLIPS rule definitions for all 23 diseases (9 Bok Choy, 14 Banana)
4. **[knowledge_base.py](knowledge_base.py)**: Symptom/disease registry with certainty factor mappings and treatments

### Data Relationships
- **SYMPTOMS dict**: Maps symptom IDs (e.g., `"dark_brown_circular_spots"`) to descriptions
- **DISEASES dict**: Maps disease names to `{"symptoms": {...}, "treatments": [...]}`; symptom values are **expert certainty factors** (0.6-1.0)
- **CF_INTERPRETATION dict**: Maps user language ("probably", "maybe not", etc.) to numerical CFs (-1.0 to 1.0)

### Certainty Factor Combination (Critical Algorithm)
In `expert_system.add_symptom()`:
- **Final CF = Expert CF × User CF**
  - Expert CF: From disease's symptom mapping (how indicative is symptom for disease)
  - User CF: From user's language (e.g., "probably" = 0.6, "definitely not" = -1.0)
- In `_extract_diseases()`: Disease CFs accumulate using formula: **Combined = CF1 + CF2 × (1 - CF1)**
  - This combines multiple symptom evidences (implemented for all symptom matches)

## Key Patterns & Conventions

### Symptom & Disease Keys
- **Symptom IDs** use snake_case: `"dark_brown_circular_spots"`, `"water_soaked_mushy"`
- **Disease names** in dicts use Title Case: `"Alternaria Leaf Spot"`
- **Symptom-Disease mappings** in DISEASES are **one-way links**: symptoms key → expert CF value
  - Disease logic in CLIPS rules determines which symptoms trigger disease detection (not hardcoded disease→symptom)

### NLP Extraction Logic (app.py)
- **Lemmatization + Lemma Matching**: Compares user's lemmatized tokens against symptom description lemmas
- **Match Threshold**: ≥ 60% overlap (match_ratio ≥ 0.6) required for symptom detection
- **Certainty Keyword Extraction**: Parses user text for CF keywords (e.g., "maybe dark spots") before lemma matching

### CLIPS Rule Pattern (rules.py)
```clips
(defrule disease-name
    (active-plant (name "Crop"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "symptom1") (eq ?n "symptom2") ...))
    (test (> ?c 0))
    =>
    (assert (disease (name "Disease Name") (certainty ?c))))
```
- Each rule fires **per symptom match** (not for all symptoms combined)
- `(test (> ?c 0))` filters out negative certainties (user said "definitely not")

## Critical Developer Workflows

### Adding a New Disease
1. Add symptoms to `SYMPTOMS` dict in [knowledge_base.py](knowledge_base.py)
2. Add disease entry in `DISEASES` dict with `symptoms` mapping (key → expert CF 0.6-1.0)
3. Create rule constant in [rules.py](rules.py) matching pattern (name convention: `DISEASE_NAME_RULE`)
4. Import rule in [expert_system.py](expert_system.py)
5. Add rule to `rules` list in `_load_rules()`

### Modifying Symptom Matching
- **NLP threshold change**: Edit `match_ratio >= 0.6` in [app.py](app.py), `extract_symptoms_manually()`
- **Stop words/lemmatization**: Modify NLTK imports/usage in `extract_symptoms_manually()`

### Testing Workflow
```bash
python -m pip install -r requirements.txt
python setup_nltk.py  # Downloads required NLTK data (must run once)
streamlit run app.py
```
- Streamlit auto-reloads on file changes; UI resets between runs
- Session state stores `expert_system` instance to avoid reinitializing on rerun

## External Dependencies & Integration Points

### CLIPS (clipspy)
- Imports: `import clips`
- Integration: `BokChoyAndBananaExpertSystem` instantiates `clips.Environment()`
- Rule syntax: CLIPS LISP-like syntax (see [rules.py](rules.py) for full grammar)
- Common errors: Template/rule name conflicts, fact assertion format errors

### NLTK
- Downloads required: Tokenizers, WordNetLemmatizer, stopwords (handled by [setup_nltk.py](setup_nltk.py))
- Used in [app.py](app.py): Tokenization, lemmatization, stop word filtering
- Note: Must match lemmas of symptom descriptions (case-insensitive comparison)

### Streamlit
- Session state pattern: `st.session_state.expert_system` persists across reruns
- Two-column layout for results: Symptoms (left) + Treatments (right)
- Widgets trigger reruns: `st.button()`, `st.selectbox()`, `st.text_area()`

## Gotchas & Important Notes

1. **Plant Type Filtering**: `expert_system.reset(plant_type)` asserts active plant; rules check `(active-plant (name "Bok Choy"))` - mismatch causes no diagnoses
2. **Negative Certainties**: User "definitely not" (-1.0) multiplied by expert CF yields negative; rules filter with `(test (> ?c 0))`
3. **Symptom Key Consistency**: Keys in `SYMPTOMS`, `DISEASES` mappings, and CLIPS rules must **exactly match** (typos block diagnosis)
4. **Empty Diagnosis Case**: Displayed when no rules fire (no symptoms matched or all CFs ≤ 0)
5. **Treatment Completeness**: Banana disease treatments are minimal placeholders (instructional); Bok Choy treatments are comprehensive
