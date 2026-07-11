import clips
from typing import List, Set
from rules import (
    SYMPTOM_TEMPLATE,
    DISEASE_TEMPLATE,
    ALTERNARIA_LEAF_SPOT_RULE,
    BACTERIAL_SOFT_ROT_RULE,
    BLACKLEG_RULE,
    BLACK_ROT_RULE,
    CLUBROOT_RULE,
    DOWNY_MILDEW_RULE,
    RHIZOCTONIA_RULE,
    PSEUDO_CERCOSPORELLA_RULE,
    TURNIP_MOSAIC_VIRUS_RULE,
    FUSARIUM_WILT_RULE
)


class BokChoyExpertSystem:    
    # Template name constant
    DISEASE_TEMPLATE = "disease"
    
    def __init__(self):
        self.env = clips.Environment()
        self._load_rules()
    
    def _load_rules(self) -> None:
        # Load CLIPS templates and rules into the environment.
        try:
            # Load templates first (required before rules)
            self.env.build(SYMPTOM_TEMPLATE.strip())
            self.env.build(DISEASE_TEMPLATE.strip())
            
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
                FUSARIUM_WILT_RULE
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
    
    def reset(self) -> None:
        # Reset the CLIPS environment, clearing all facts.
        self.env.reset()
    
    def add_symptom(self, symptom_name: str, present: bool = True) -> None:
        # Add a symptom fact to the knowledge base.
        present_value = "yes" if present else "no"
        fact_string = f'(symptom (name "{symptom_name}") (present {present_value}))'
        
        try:
            self.env.assert_string(fact_string)
        except clips.CLIPSError as e:
            raise ValueError(
                f"Invalid symptom fact format: {fact_string}\n"
                f"CLIPS Error: {e}"
            ) from e
    
    def diagnose(self) -> List[str]:
        # Run the inference engine and return diagnosed diseases.
        self.env.run()
        
        diseases = self._extract_diseases()
        
        return diseases
    
    def _extract_diseases(self) -> List[str]:        
        # Extract unique disease names from the current facts.
        diseases: List[str] = []
        seen_diseases: Set[str] = set()
        
        for fact in self.env.facts():
            if fact.template.name == self.DISEASE_TEMPLATE:
                disease_name = fact["name"]
                
                # Only add each disease once
                if disease_name not in seen_diseases:
                    diseases.append(disease_name)
                    seen_diseases.add(disease_name)
        
        return diseases
    
    def get_all_facts(self) -> List[str]:
        # Get all facts currently in the environment as strings.
        return [str(fact) for fact in self.env.facts()]