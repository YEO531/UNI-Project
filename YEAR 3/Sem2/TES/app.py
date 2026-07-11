import streamlit as st
import pandas as pd
import numpy as np

# App title
st.title("Streamlit Test Application")

# Sidebar
st.sidebar.header("User Input Panel")

name = st.sidebar.text_input("Enter your name", "Student")
age = st.sidebar.slider("Select your age", 18, 60, 22)

# Main content
st.write(f"### Hello, {name} 👋")
st.write(f"You are **{age}** years old.")

# Button test
if st.button("Click me"):
    st.success("Button clicked successfully!")

# Checkbox test
if st.checkbox("Show sample data"):
    st.subheader("Sample Data Table")
    df = pd.DataFrame(
        np.random.randn(10, 3),
        columns=["Feature A", "Feature B", "Feature C"]
    )
    st.dataframe(df)

# Selectbox test
option = st.selectbox(
    "Choose a model type",
    ("Machine Learning", "Deep Learning", "Expert System")
)
st.write("You selected:", option)

# Chart test
st.subheader("Sample Line Chart")
chart_data = pd.DataFrame(
    np.random.randn(20, 2),
    columns=["Accuracy", "Loss"]
)
st.line_chart(chart_data)

# Footer
st.markdown("---")
st.caption("Streamlit testing app – working correctly ✅")
