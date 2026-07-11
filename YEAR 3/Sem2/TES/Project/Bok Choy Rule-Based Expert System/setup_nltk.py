import nltk

# These commands will download the specific datasets required for tokenization and lemmatization
nltk.download('punkt')      # For word_tokenize()
nltk.download('punkt_tab')
nltk.download('wordnet')    # For WordNetLemmatizer()
nltk.download('stopwords')  # For removing common words like 'the', 'is', etc.