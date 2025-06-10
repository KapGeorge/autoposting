import tkinter as tk
from tkinter import messagebox
from pytrends.request import TrendReq


# Core logic to fetch trends using pytrends

def fetch_trends(country_code):
    try:
        pytrends = TrendReq(hl='en-US', tz=360)
        trending_searches = pytrends.trending_searches(pn=country_code.lower())
        trends = trending_searches[0].tolist()[:10]
        return trends if trends else ["trends not found"]
    except Exception as e:
        return [f"error connection: {e}"]


# GUI Interface

def show_trends():
    country = country_entry.get().strip()

    if not country:
        messagebox.showerror("error", "enter country code")
        return

    trends = fetch_trends(country)
    result_text.delete("1.0", tk.END)
    for i, trend in enumerate(trends, 1):
        result_text.insert(tk.END, f"{i}. {trend}\n")

# Setup window
root = tk.Tk()
root.title("Google Trends Viewer")
root.geometry("500x400")

# Inputs
frame = tk.Frame(root)
frame.pack(pady=10)

country_label = tk.Label(frame, text="country code:")
country_label.grid(row=0, column=0, padx=5)
country_entry = tk.Entry(frame)
country_entry.grid(row=0, column=1, padx=5)

fetch_button = tk.Button(root, text="get trends", command=show_trends)
fetch_button.pack(pady=5)

# Output
result_text = tk.Text(root, wrap=tk.WORD, height=15)
result_text.pack(padx=10, pady=10, fill=tk.BOTH, expand=True)

root.mainloop()