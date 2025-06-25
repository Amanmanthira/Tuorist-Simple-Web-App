from flask import Flask, request, jsonify, render_template
from transformers import pipeline
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

generator = pipeline('text-generation', model='EleutherAI/gpt-neo-125M')
faq_data = """
Q: Hi
A: Hello! How can I assist you with Sri Lanka tourism today?

Q: Hello
A: Hi there! Feel free to ask me anything about visiting Sri Lanka.

Q: Hola
A: Hola! Ready to explore the beautiful island of Sri Lanka?

Q: Hey
A: Hey! What would you like to know about Sri Lanka?

Q: What is the best time to visit Sri Lanka?
A: The best time to visit Sri Lanka is from December to March for the west and south coast, and from May to September for the east coast.

Q: What are the must-see places in Sri Lanka?
A: Some must-see places include Sigiriya Rock Fortress, Yala National Park, Ella, Galle Fort, and the cultural triangle.

Q: Is it safe to travel in Sri Lanka?
A: Sri Lanka is generally safe for tourists, but always stay updated with travel advisories.

Q: What currency is used in Sri Lanka?
A: The currency used is Sri Lankan Rupee (LKR).

Q: What languages are spoken in Sri Lanka?
A: Sinhala and Tamil are official languages, and English is widely spoken.

Q: Can you tell me about your site?
A: Our site offers detailed travel guides, booking services, and local insights for tourists visiting Sri Lanka.

Q: What kind of food can I try in Sri Lanka?
A: Sri Lanka offers delicious cuisine like rice and curry, hoppers, kottu, and seafood specialties.

Q: How do I get around in Sri Lanka?
A: You can use buses, trains, tuk-tuks, or rent cars to travel around the country.

Q: Do I need a visa to visit Sri Lanka?
A: Many travelers require an Electronic Travel Authorization (ETA) before arrival. Check your country's requirements.

Q: Are there any cultural customs I should know?
A: Yes! Dress modestly when visiting temples, remove shoes before entering homes and religious sites, and be respectful of local traditions.

Q: What wildlife can I see in Sri Lanka?
A: You can see elephants, leopards, monkeys, various birds, and marine life in national parks and reserves.

Q: Can you recommend some beaches in Sri Lanka?
A: Yes! Try Unawatuna, Mirissa, Bentota, Arugam Bay, and Nilaveli for beautiful beaches and water sports.

Q: How is the weather in Sri Lanka?
A: Sri Lanka has a tropical climate with warm temperatures year-round. Rainfall varies by region and season.

Q: What festivals are celebrated in Sri Lanka?
A: Some popular festivals are Sinhala and Tamil New Year, Vesak (Buddha’s birthday), Diwali, and Christmas.

Q: Are there good hiking trails in Sri Lanka?
A: Yes! You can hike in Ella (Little Adam's Peak), Horton Plains (World's End), and Knuckles Mountain Range.

Q: What accommodations are available?
A: Sri Lanka has everything from budget hostels and guesthouses to luxury hotels and resorts.

Q: How safe is the food and water?
A: Eat at trusted places and drink bottled or filtered water to avoid stomach issues.

Q: Can you help me plan my trip?
A: Sure! Let me know your interests and travel dates, and I can suggest places and activities.

Q: Does your site offer booking services?
A: Yes! You can book hotels, tours, and transport directly through our site.

Q: Do you have travel tips for Sri Lanka?
A: Always carry cash, respect local customs, dress modestly in religious places, and keep copies of important documents.

Q: What’s special about Sri Lankan culture?
A: Sri Lanka is known for its rich history, ancient temples, vibrant festivals, traditional dance, and warm hospitality.

Q: Can I use credit cards in Sri Lanka?
A: Credit cards are widely accepted in cities, but carry cash for rural areas and small shops.

Q: How reliable is internet access?
A: Internet is good in cities and tourist areas but may be slower or limited in remote regions.

Q: What wildlife safaris can I take?
A: Yala, Udawalawe, and Wilpattu National Parks offer excellent safari experiences to see elephants, leopards, and more.

Q: What are the top cultural sites?
A: Visit Anuradhapura, Polonnaruwa, Dambulla Cave Temple, and the Temple of the Tooth in Kandy.

"""


qa_pairs = [pair.strip() for pair in faq_data.strip().split('\n\n')]
questions = [q.split('\n')[0][3:].strip() for q in qa_pairs]
answers = [a.split('\n')[1][3:].strip() for a in qa_pairs]

vectorizer = TfidfVectorizer().fit(questions)
question_vectors = vectorizer.transform(questions)

def find_best_answer(user_question):
    user_vec = vectorizer.transform([user_question])
    similarities = cosine_similarity(user_vec, question_vectors)
    best_idx = similarities.argmax()
    score = similarities[0, best_idx]
    if score < 0.3:
        return None
    return answers[best_idx]

@app.route('/')
def home():
    return render_template('chatbot.html')

@app.route('/chat', methods=['POST'])
def chat():
    user_message = request.json.get('message')
    if not user_message:
        return jsonify({'response': 'Please say something!'})
    
    answer = find_best_answer(user_message)
    if answer:
        return jsonify({'response': answer})
    
    prompt = f"User asked: {user_message}\nAnswer as a helpful Sri Lanka travel assistant:"
    response = generator(prompt, max_length=100, do_sample=True, temperature=0.7)[0]['generated_text']
    generated_answer = response[len(prompt):].strip()
    
    return jsonify({'response': generated_answer})

if __name__ == '__main__':
    app.run(debug=True)
