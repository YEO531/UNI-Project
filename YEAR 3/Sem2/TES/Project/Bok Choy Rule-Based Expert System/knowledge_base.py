SYMPTOMS = {
    # Bok Choy Symptoms
    "dark_brown_circular_spots": "Dark brown circular leaf spots",
    "concentric_rings": "Concentric rings",
    "yellowing_premature_drop": "Yellowing and premature leaf drop",
    "water_soaked_mushy": "Water-soaked, mushy tissues",
    "soft_decay_stem_base": "Soft decay at stem or leaf base",
    "foul_odor": "Foul odor",
    "sudden_collapse": "Sudden plant collapse",
    "gray_lesions_stems": "Gray lesions on stems",
    "black_purple_margins": "Black or purple margins on lesions",
    "blackened_roots_stems": "Blackened roots or lower stems",
    "seedling_damping_off": "Seedling damping-off",
    "v_shaped_yellow_lesions": "V-shaped yellow lesions from leaf edges",
    "blackened_veins": "Blackened veins",
    "wilting_stunted": "Wilting and stunted growth",
    "stunted_growth": "Stunted growth",
    "wilting_hot_periods": "Wilting during hot periods",
    "swollen_club_roots": "Swollen, club-shaped roots",
    "yellow_patches_upper": "Yellow patches on upper leaf surface",
    "gray_purple_fuzzy_under": "Gray-purple fuzzy growth under leaves",
    "leaf_browning_death": "Leaf browning and death",
    "seedling_collapse_soil_line": "Seedlings collapse at soil line",
    "brown_stem_lesions": "Brown stem lesions",
    "bottom_leaf_rot": "Bottom leaf rot where leaves touch soil",
    "small_gray_white_spots": "Small gray or white leaf spots",
    "angular_irregular_lesions": "Angular or irregular lesions",
    "leaf_yellowing_defoliation": "Leaf yellowing and defoliation",
    "mosaic_patterns": "Mosaic patterns on leaves",
    "leaf_distortion": "Leaf distortion",
    "wilting_warm_weather": "Wilting during warm weather",
    "yellowing_lower_leaves": "Yellowing of lower leaves",
    "vascular_discoloration": "Vascular discoloration",
    # Banana Symptoms
    "blackish_brown_lines": "Blackish brown lines on the leaf blade", 
    "elongated_spots_leaf": "Elongated spots on the leaf blades 2x20 mm", 
    "leaf_spots_oval_elongated": "Leaf spots enlarge in oval or elongated shape", 
    "yellow_circle_on_edge_of_spot": "The yellow circle on the edge of the spot", 
    "leaves_dry_out_necrosis": "Some leaves or entire leaves dry out and experience necrosis", 
    "fruit_not_develop_ripens_quickly": "Fruit does not develop and ripens more quickly", 
    "yellow_to_pale_brown_spots": "Yellow to pale brown spots", 
    "spots_central_circles_necrosis": "Spots with central circles of necrosis are gray or brownish-red", 
    "spots_edges_leaves": "Spots on the edges of the leaves and progress towards the mother of the leaf veins", 
    "spots_join_yellow_dry": "The spots join, so the leaves turn yellow and dry", 
    "cross_shaped_black_spots": "Black spots with four corners so that they are cross-shaped", 
    "spots_extend_leaf_veins": "The spots extend in the direction of the leaf veins", 
    "spots_spread_randomly": "The spots spread randomly", 
    "leaves_dry_out": "Leaves dry out", 
    "leaves_turn_yellow": "Leaves turn yellow", 
    "pseudostem_splits_breaks": "The pseudostem splits or breaks", 
    "brown_dots_stem_cut": "There are brown dots on the stem if cut crosswise or lengthwise", 
    "necrosis_on_tuber": "There is necrosis on the tuber", 
    "heart_rots_dries": "The heart rots and dries up", 
    "rotten_flesh": "Rotten flesh", 
    "rotten_weevil": "Rotten weevil", 
    "foul_odor_banana": "Smells bad (Foul odor)", 
    "leaves_shrink": "Leaves shrink", 
    "pale_leaves": "Pale Leaves", 
    "spots_mother_bone_leaves": "There are spots on the mother of the bone leaves", 
    "dwarf_plant": "Dwarf plant", 
    "miserable_growth": "Plants grow miserable", 
    "slow_growth": "Slow growth", 
    "leaves_torn_curled": "The leaves are torn and curled", 
    "rolls_leaves_dry": "Rolls of leaves dry out", 
    "greenish_white_caterpillars": "There are greenish-white caterpillars", 
    "small_white_spots": "There are small white spots on the leaves", 
    "black_spots_fruit": "Black spots on the fruit", 
    "dark_brown_sunken_spots": "There are dark brown sunken spots on the fruit", 
    "orange_pink_mushrooms": "There are orange to pink mushrooms", 
    "white_reddish_brown_mucus": "There is white to reddish brown mucus" 
}

DISEASES = {
    "Alternaria Leaf Spot": {
        "symptoms": {
            "dark_brown_circular_spots": 0.8,
            "concentric_rings": 1.0,
            "yellowing_premature_drop": 0.6
        },
        "treatments": [
            "Use disease-free seeds to prevent initial infection.",
            "Remove infected leaves and debris from the garden to reduce disease spread.",
            "Practice crop rotation with non-Brassica crops to break the disease cycle.",
            "Improve air circulation around plants by proper spacing.",
            "Apply fungicide such as chlorothalonil according to label instructions."
        ]
    },
    "Bacterial Soft Rot": {
        "symptoms": {
            "water_soaked_mushy": 1.0,
            "soft_decay_stem_base": 0.8,
            "foul_odor": 0.8,
            "sudden_collapse": 0.6
        },
        "treatments": [
            "There is no curative treatment available for bacterial soft rot.",
            "Remove and destroy infected plants immediately to prevent spread.",
            "Improve drainage to reduce excess moisture that favors bacterial growth.",
            "Avoid plant injury during cultivation as wounds provide entry points for bacteria.",
            "Sanitize all gardening tools between uses to prevent disease transmission."
        ]
    },
    "Blackleg": {
        "symptoms": {
            "gray_lesions_stems": 0.8,
            "black_purple_margins": 1.0,
            "blackened_roots_stems": 0.8,
            "seedling_damping_off": 0.6
        },
        "treatments": [
            "Use disease-free seeds from reputable sources.",
            "Remove infected residues from previous crops completely.",
            "Practice crop rotation to reduce pathogen buildup in soil.",
            "Maintain good field sanitation by removing plant debris regularly."
        ]
    },
    "Black Rot": {
        "symptoms": {
            "v_shaped_yellow_lesions": 1.0,
            "blackened_veins": 0.8,
            "wilting_stunted": 0.6
        },
        "treatments": [
            "Use certified disease-free seed to ensure healthy transplants.",
            "Apply hot-water seed treatment to eliminate seed-borne pathogens.",
            "Practice crop rotation with non-cruciferous crops for at least two years.",
            "Avoid overhead irrigation which can spread bacteria through water splash.",
            "Remove infected plants promptly to reduce inoculum in the field."
        ]
    },
    "Clubroot": {
        "symptoms": {
            "stunted_growth": 0.6,
            "wilting_hot_periods": 0.6,
            "swollen_club_roots": 1.0
        },
        "treatments": [
            "Apply lime to raise soil pH above 7.2 which inhibits pathogen activity.",
            "Implement long crop rotation of 3 to 7 years with non-Brassica crops.",
            "Improve drainage to reduce soil moisture that favors disease development.",
            "Plant resistant varieties when available for better disease management."
        ]
    },
    "Downy Mildew": {
       "symptoms": {
            "yellow_patches_upper": 0.8,
            "gray_purple_fuzzy_under": 1.0,
            "leaf_browning_death": 0.6
        },
        "treatments": [
            "Reduce humidity around plants by avoiding overcrowding.",
            "Avoid overhead watering and water early in the day to allow foliage to dry.",
            "Improve air circulation through proper plant spacing and pruning.",
            "Apply appropriate fungicides labeled for downy mildew control when necessary."
        ]
    },
    "Rhizoctonia Bottom Rot & Damping-Off": {
        "symptoms": {
            "seedlings_collapse_soil_line": 0.8,
            "brown_stem_lesions": 0.8,
            "bottom_leaf_rot": 1.0
        },
        "treatments": [
            "Use well-drained soil and raised beds to prevent waterlogging.",
            "Avoid overwatering as excessive moisture promotes fungal growth.",
            "Remove infected seedlings immediately to prevent disease spread.",
            "Sanitize soil and tools before planting to reduce pathogen presence."
        ]
    },
    "Pseudo-Cercosporella Leaf Spot": {
        "symptoms": {
            "small_gray_white_spots": 0.8,
            "angular_irregular_lesions": 0.8,
            "leaf_yellowing_defoliation": 0.6
        },
        "treatments": [
            "Remove infected leaves as soon as symptoms appear.",
            "Avoid prolonged leaf wetness by watering at the base of plants.",
            "Practice crop rotation to reduce disease recurrence.",
            "Apply fungicides if the disease becomes severe and threatens the crop."
        ]
    },
    "Turnip Mosaic Virus": {
       "symptoms": {
            "mosaic_patterns": 1.0,
            "leaf_distortion": 0.8,
            "stunted_growth": 0.6
        },
        "treatments": [
            "Remove infected plants immediately as viruses cannot be cured.",
            "Control aphids which serve as vectors for virus transmission using insecticidal soaps or oils.",
            "Use insect netting or row covers to exclude aphid vectors from plants.",
            "Start with virus-free planting material from certified sources."
        ]
    },
    "Bok Choy Fusarium Wilt": {
        "symptoms": {
            "wilting_warm_weather": 0.8,
            "yellowing_lower_leaves": 0.6,
            "vascular_discoloration": 1.0
        },
        "treatments": [
            "Practice crop rotation with non-susceptible crops for several years.",
            "Improve drainage to reduce favorable conditions for the pathogen.",
            "Use soil solarization during hot months to reduce soil-borne pathogen populations.",
            "Plant resistant varieties when available for long-term disease management."
        ]
    },
    
    # Banana Diseases 
    "Sigatoka Fungal Disease": {
        "symptoms": {
            "spots_spread_randomly": 0.8, 
            "yellow_circle_on_edge_of_spot": 1.0, 
            "leaf_spots_oval_elongated": 0.8
        },
        "treatments": ["Apply expertise in plant pathology.", "Monitor leaf blade lines."]
    },
    "Cordana leaf spot": {
        "symptoms": {
            "spots_spread_randomly": 0.8,
            "yellow_circle_on_edge_of_spot": 1.0,
            "yellow_to_pale_brown_spots": 0.8
        },
        "treatments": ["Consult agricultural instructors for guidance"]
    },
    "Crossed spot disease": {
        "symptoms": {
            "spots_spread_randomly": 0.8, 
            "fruit_not_develop_ripens_quickly": 1.0
        },
        "treatments": ["Monitor fruit development closely."]
    },
    "Banana Fusarium Wilt Disease": {
        "symptoms": {
            "leaves_dry_out": 0.6,  
            "yellow_to_pale_brown_spots": 1.0  
        },
        "treatments": ["Identify symptoms early."]
    },
    "Blood Diseases": {
        "symptoms": {
            "slow_growth": 0.6, 
            "rotten_weevil": 0.8,  
            "rotten_flesh": 1.0, 
            "spots_central_circles_necrosis": 1.0 
        },
        "treatments": ["Check for brown dots on stems."]
    },
    "Banana dwarf": {
        "symptoms": {
            "slow_growth": 0.6, 
            "leaves_shrink": 1.0 
        },
        "treatments": ["Monitor growth rates."]
    },
    "Anthracnose Disease": {
        "symptoms": {
            "slow_growth": 0.6, 
            "rotten_weevil": 0.8, 
            "rotten_flesh": 1.0, 
            "leaf_spots_oval_elongated": 0.8
        },
        "treatments": ["Check for dark brown sunken spots."]
    },
    "Aphids": {
        "symptoms": {
            "leaves_dry_out": 0.6, 
            "leaves_dry_out_necrosis": 1.0
        },
        "treatments": ["Monitor for leaf drying."]
    },
    "Banana leaf roller pest": {
        "symptoms": {
            "leaves_turn_yellow": 1.0 
        },
        "treatments": ["Inspect for torn/curled leaves."]
    },
    "Fruit scab pest": {
        "symptoms": {
            "greenish_white_caterpillars": 1.0
        },
        "treatments": ["Check for black spots on fruit."]
    }
}

# Values based on provided Certainty Theory interpretation table
CF_INTERPRETATION = {
    "definitely not": -1.0,
    "almost certainly not": -0.8, 
    "probably not": -0.6,        
    "maybe not": -0.4,             
    "unknown": 0.0,                
    "maybe": 0.4,                  
    "probably": 0.6,              
    "almost certainly": 0.8,      
    "yes": 1.0
}