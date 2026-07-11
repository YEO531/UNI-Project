# Template for facts
SYMPTOM_TEMPLATE = "(deftemplate symptom (slot name) (slot present (default no)))"
DISEASE_TEMPLATE = "(deftemplate disease (slot name))"
TEMPLATES = SYMPTOM_TEMPLATE + "\n" + DISEASE_TEMPLATE

# Rules for diseases
ALTERNARIA_LEAF_SPOT_RULE = """
(defrule alternaria-leaf-spot
    (symptom (name "dark_brown_circular_spots") (present yes))
    (symptom (name "concentric_rings") (present yes))
    (symptom (name "yellowing_premature_drop") (present yes))
    =>
    (assert (disease (name "Alternaria Leaf Spot"))))
"""

BACTERIAL_SOFT_ROT_RULE = """
(defrule bacterial-soft-rot
    (symptom (name "water_soaked_mushy") (present yes))
    (symptom (name "soft_decay_stem_base") (present yes))
    (symptom (name "foul_odor") (present yes))
    (symptom (name "sudden_collapse") (present yes))
    =>
    (assert (disease (name "Bacterial Soft Rot"))))
"""

BLACKLEG_RULE = """
(defrule blackleg
    (symptom (name "gray_lesions_stems") (present yes))
    (symptom (name "black_purple_margins") (present yes))
    (symptom (name "blackened_roots_stems") (present yes))
    (symptom (name "seedling_damping_off") (present yes))
    =>
    (assert (disease (name "Blackleg"))))
"""

BLACK_ROT_RULE = """
(defrule black-rot
    (symptom (name "v_shaped_yellow_lesions") (present yes))
    (symptom (name "blackened_veins") (present yes))
    (symptom (name "wilting_stunted") (present yes))
    =>
    (assert (disease (name "Black Rot"))))
"""

CLUBROOT_RULE = """
(defrule clubroot
    (symptom (name "swollen_club_roots") (present yes))
    (symptom (name "stunted_growth") (present yes))
    (symptom (name "wilting_hot_periods") (present yes))
    =>
    (assert (disease (name "Clubroot"))))
"""

DOWNY_MILDEW_RULE = """
(defrule downy-mildew
    (symptom (name "yellow_patches_upper") (present yes))
    (symptom (name "gray_purple_fuzzy_under") (present yes))
    (symptom (name "leaf_browning_death") (present yes))
    =>
    (assert (disease (name "Downy Mildew"))))
"""

RHIZOCTONIA_RULE = """
(defrule rhizoctonia-bottom-rot
    (symptom (name "seedling_collapse_soil_line") (present yes))
    (symptom (name "brown_stem_lesions") (present yes))
    (symptom (name "bottom_leaf_rot") (present yes))
    =>
    (assert (disease (name "Rhizoctonia Bottom Rot & Damping-Off"))))
"""

PSEUDO_CERCOSPORELLA_RULE = """
(defrule pseudo-cercosporella
    (symptom (name "small_gray_white_spots") (present yes))
    (symptom (name "angular_irregular_lesions") (present yes))
    (symptom (name "leaf_yellowing_defoliation") (present yes))
    =>
    (assert (disease (name "Pseudo-Cercosporella Leaf Spot"))))
"""

TURNIP_MOSAIC_VIRUS_RULE = """
(defrule turnip-mosaic-virus
    (symptom (name "mosaic_patterns") (present yes))
    (symptom (name "leaf_distortion") (present yes))
    =>
    (assert (disease (name "Turnip Mosaic Virus"))))
"""

FUSARIUM_WILT_RULE = """
(defrule fusarium-wilt
    (symptom (name "wilting_warm_weather") (present yes))
    (symptom (name "yellowing_lower_leaves") (present yes))
    (symptom (name "vascular_discoloration") (present yes))
    =>
    (assert (disease (name "Fusarium Wilt"))))
"""