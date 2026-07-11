from ast import keyword
import clips
from typing import List, Set
from knowledge_base import CF_INTERPRETATION, DISEASES
from rules import (
    SYMPTOM_TEMPLATE,
    DISEASE_TEMPLATE,
    PLANT_TEMPLATE,
    ALTERNARIA_LEAF_SPOT_RULE,
    BACTERIAL_SOFT_ROT_RULE,
    BLACKLEG_RULE,
    BLACK_ROT_RULE,
    CLUBROOT_RULE,
    DOWNY_MILDEW_RULE,
    RHIZOCTONIA_RULE,
    PSEUDO_CERCOSPORELLA_RULE,
    TURNIP_MOSAIC_VIRUS_RULE,
    BOK_CHOY_FUSARIUM_WILT_RULE,
    SIGATOKA_FUNGAL_RULE,
    CORDANA_LEAF_SPOT_RULE,
    CROSSED_SPOT_RULE,
    BANANA_FUSARIUM_WILT_RULE,
    APHIDS_RULE,
    BANANA_DWARF_RULE,
    ANTHRACNOSE_RULE,
    BLOOD_DISEASES_RULE,
    LEAF_ROLLER_RULE,
    FRUIT_SCAB_RULE 
)


class BokChoyAndBananaExpertSystem:    
    DISEASE_TEMPLATE = "disease"
    
    def __init__(self):
        self.env = clips.Environment()
        self._load_rules()
    
    def _load_rules(self) -> None:
        # Load CLIPS templates and rules into the environment.
        try:
            # Load templates first 
            self.env.build(SYMPTOM_TEMPLATE.strip())
            self.env.build(DISEASE_TEMPLATE.strip())
            self.env.build(PLANT_TEMPLATE.strip())
            
            # Load each rule individually
            rules = [
                ALTERNARIA_LEAF_SPOT_RULE,
                BACTERIAL_SOFT_ROT_RULE,
                BLACKLEG_RULE,
                BLACK_ROT_RULE,
                CLUBROOT_RULE,
                DOWNY_MILDEW_RULE,
                RHIZOCTONIA_RULE,
                PSEUDO_CERCOSPORELLA_RULE,
                TURNIP_MOSAIC_VIRUS_RULE,
                BOK_CHOY_FUSARIUM_WILT_RULE,
                SIGATOKA_FUNGAL_RULE,
                CORDANA_LEAF_SPOT_RULE,
                CROSSED_SPOT_RULE,
                BANANA_FUSARIUM_WILT_RULE,
                APHIDS_RULE,
                BANANA_DWARF_RULE,
                ANTHRACNOSE_RULE,
                BLOOD_DISEASES_RULE,
                LEAF_ROLLER_RULE,
                FRUIT_SCAB_RULE 
            ]
            
            for rule in rules:
                self.env.build(rule.strip())
                
        except clips.CLIPSError as e:
            raise
        except Exception as e:
            error_msg = (
                f"Unexpected error while loading CLIPS rules: {e}\n"
                f"Error type: {type(e).__name__}"
            )
            raise RuntimeError(error_msg) from e
    
    def reset(self, plant_type: str) -> None:
        # Reset the CLIPS environment, clearing all facts.
        self.env.reset()
        self.env.assert_string(f'(active-plant (name "{plant_type}"))')
    
    def diagnose(self) -> dict:
        # Run the inference engine and return diagnosed diseases.
        self.env.run()
        
        diseases = self._extract_diseases()
        
        return diseases
    
    def _extract_diseases(self) -> dict:        
        diagnoses = {}
        for fact in self.env.facts():
            if fact.template.name == self.DISEASE_TEMPLATE:
                name = fact["name"]
                new_cf = float(fact["certainty"])
            
                if name not in diagnoses:
                    diagnoses[name] = new_cf
                else:
                    # Accumulate evidence using the CF combination formula
                    old_cf = diagnoses[name]
                    # Combined = CF1 + CF2 * (1 - CF1)
                    diagnoses[name] = old_cf + (new_cf * (1.0 - old_cf))
    
        # Return sorted results by certainty
        return dict(sorted(diagnoses.items(), key=lambda item: item[1], reverse=True))
    
    def get_all_facts(self) -> List[str]:
        return [str(fact) for fact in self.env.facts()]
    
    def add_symptom(self, symptom_name: str, certainty_keyword: str, plant_type: str) -> None:
        if isinstance(certainty_keyword, bool):
            certainty_keyword = "yes" if certainty_keyword else "no"
    
        # Map certainty keyword to numerical value
        keyword = certainty_keyword.lower().strip()
    
        user_cf = CF_INTERPRETATION.get(keyword, 0.0)
    
        expert_cf = 1.0 
    
        # Filter diseases by plant type
        for disease, info in DISEASES.items():
            if symptom_name in info["symptoms"]:
                expert_cf = info["symptoms"][symptom_name]
                break
        
        # Combine user and expert certainty factors
        final_cf = expert_cf * user_cf
    
        fact_string = f'(symptom (name "{symptom_name}") (cf {float(final_cf)}))'
    
        try:
            self.env.assert_string(fact_string)
        except Exception as e:
            print(f"Error asserting symptom: {e}")
        