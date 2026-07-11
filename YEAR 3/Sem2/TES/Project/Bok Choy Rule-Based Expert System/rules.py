# Template for facts
SYMPTOM_TEMPLATE = "(deftemplate symptom (slot name) (slot cf (type FLOAT)))"
DISEASE_TEMPLATE = "(deftemplate disease (slot name) (slot certainty (type FLOAT)))"
PLANT_TEMPLATE = "(deftemplate active-plant (slot name))"
TEMPLATES = SYMPTOM_TEMPLATE + "\n" + DISEASE_TEMPLATE + "\n" + PLANT_TEMPLATE

# Rules for diseases
# Bok Choy Rules
ALTERNARIA_LEAF_SPOT_RULE = """
(defrule alternaria-leaf-spot
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "dark_brown_circular_spots")
              (eq ?n "concentric_rings")
              (eq ?n "yellowing_premature_drop")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Alternaria Leaf Spot") (certainty ?c))))
"""

BACTERIAL_SOFT_ROT_RULE = """
(defrule bacterial-soft-rot
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "water_soaked_mushy")
              (eq ?n "soft_decay_stem_base")
              (eq ?n "foul_odor")
              (eq ?n "sudden_collapse")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Bacterial Soft Rot") (certainty ?c))))
"""

BLACKLEG_RULE = """
(defrule blackleg
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "gray_lesions_stems")
              (eq ?n "black_purple_margins")
              (eq ?n "blackened_roots_stems")
              (eq ?n "seedling_damping_off")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Blackleg") (certainty ?c))))
"""

BLACK_ROT_RULE = """
(defrule black-rot
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "v_shaped_yellow_lesions")
              (eq ?n "blackened_veins")
              (eq ?n "wilting_stunted")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Black Rot") (certainty ?c))))
"""

CLUBROOT_RULE = """
(defrule clubroot
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "swollen_club_roots")
              (eq ?n "stunted_growth")
              (eq ?n "wilting_hot_periods")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Clubroot") (certainty ?c))))
"""

DOWNY_MILDEW_RULE = """
(defrule downy-mildew
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "yellow_patches_upper")
              (eq ?n "gray_purple_fuzzy_under")
              (eq ?n "leaf_browning_death")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Downy Mildew") (certainty ?c))))
"""

RHIZOCTONIA_RULE = """
(defrule rhizoctonia-bottom-rot
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "seedling_collapse_soil_line")
              (eq ?n "brown_stem_lesions")
              (eq ?n "bottom_leaf_rot")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Rhizoctonia Bottom Rot & Damping-Off") (certainty ?c))))
"""

PSEUDO_CERCOSPORELLA_RULE = """
(defrule pseudo-cercosporella
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "small_gray_white_spots")
              (eq ?n "angular_irregular_lesions")
              (eq ?n "leaf_yellowing_defoliation")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Pseudo-Cercosporella Leaf Spot") (certainty ?c))))
"""

TURNIP_MOSAIC_VIRUS_RULE = """
(defrule turnip-mosaic-virus
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "mosaic_patterns")
              (eq ?n "leaf_distortion")
              (eq ?n "stunted_growth")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Turnip Mosaic Virus") (certainty ?c))))
"""

BOK_CHOY_FUSARIUM_WILT_RULE = """
(defrule bok-choy-fusarium-wilt
    (active-plant (name "Bok Choy"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "wilting_warm_weather")
              (eq ?n "yellowing_lower_leaves")
              (eq ?n "vascular_discoloration")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Bok Choy Fusarium Wilt") (certainty ?c))))
"""

# Banana Rules
SIGATOKA_FUNGAL_RULE = """
(defrule sigatoka-fungal-disease
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "spots_spread_randomly")
              (eq ?n "yellow_circle_on_edge_of_spot")
              (eq ?n "leaf_spots_oval_elongated")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Sigatoka Fungal Disease") (certainty ?c))))
"""

CORDANA_LEAF_SPOT_RULE = """
(defrule cordana-leaf-spot
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "spots_spread_randomly")
              (eq ?n "yellow_circle_on_edge_of_spot")
              (eq ?n "yellow_to_pale_brown_spots")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Cordana Leaf Spot") (certainty ?c))))
"""

CROSSED_SPOT_RULE = """
(defrule crossed-spot-disease
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "spots_spread_randomly")
              (eq ?n "fruit_not_develop_ripens_quickly")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Crossed spot disease") (certainty ?c))))
"""

BANANA_FUSARIUM_WILT_RULE = """
(defrule fusarium-wilt-banana
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "leaves_dry_out")
              (eq ?n "yellow_to_pale_brown_spots")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Banana Fusarium Wilt Disease") (certainty ?c))))
"""

APHIDS_RULE = """
(defrule aphids
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "leaves_dry_out")
              (eq ?n "leaves_dry_out_necrosis")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Aphids") (certainty ?c))))
"""

BANANA_DWARF_RULE = """
(defrule banana-dwarf
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "slow_growth")
              (eq ?n "leaves_shrink")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Banana dwarf") (certainty ?c))))
"""

ANTHRACNOSE_RULE = """
(defrule anthracnose-disease
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "slow_growth")
              (eq ?n "rotten_weevil")
              (eq ?n "rotten_flesh")
              (eq ?n "leaf_spots_oval_elongated")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Anthracnose Disease") (certainty ?c))))
"""

BLOOD_DISEASES_RULE = """
(defrule blood-diseases
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (or (eq ?n "slow_growth")
              (eq ?n "rotten_weevil")
              (eq ?n "rotten_flesh")
              (eq ?n "spots_central_circles_necrosis")))
    (test (> ?c 0))
    =>
    (assert (disease (name "Blood Diseases") (certainty ?c))))
"""

LEAF_ROLLER_RULE = """
(defrule banana-leaf-roller-pest
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (eq ?n "leaves_turn_yellow"))
    (test (> ?c 0))
    =>
    (assert (disease (name "Banana leaf roller pest") (certainty ?c))))
"""

FRUIT_SCAB_RULE = """
(defrule fruit-scab-pest
    (active-plant (name "Banana"))
    (symptom (name ?n) (cf ?c))
    (test (eq ?n "greenish_white_caterpillars"))
    (test (> ?c 0))
    =>
    (assert (disease (name "Fruit scab pest") (certainty ?c))))
"""