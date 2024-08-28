from flask import Flask, render_template, request, jsonify
import json
import os

app = Flask(__name__)

@app.route('/')
def index():
    dataset = request.args.get("dataset")
    drag_sensitivity = float(request.args.get("dragSensitivity", 0.2))
    start_index = int(request.args.get("startIndex", 0))

    json_path = os.path.join("static","data", dataset, "images.json")
    print(json_path)
    with open(json_path, 'r') as f:
        image_filenames = json.load(f)  # Ensure this is a list
    print(type(image_filenames))
    return render_template('index.html', 
                           image_filenames=image_filenames, 
                           dataset=dataset, 
                           drag_sensitivity=drag_sensitivity, 
                           start_index=start_index)


if __name__ == '__main__':
    app.run(debug=True)
