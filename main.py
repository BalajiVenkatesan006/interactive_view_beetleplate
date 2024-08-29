from flask import Flask, render_template, request, jsonify
import json
import os

app = Flask(__name__)

def select_every_nth(array, n):
    """Select every nth element from the list."""
    return array[n-1::n]

def get_image_paths_for_dataset(directory):
    """Get and sort image paths numerically based on filename."""
    images = [f for f in os.listdir(directory) if f.endswith('.jpg')]
    images.sort(key=lambda x: int(os.path.splitext(x)[0]))
    return images

def create_json_files(base_directory, n=1):
    """Generate JSON files for each dataset in the base directory."""
    datasets = [d for d in os.listdir(base_directory) if os.path.isdir(os.path.join(base_directory, d))]
    
    for dataset in datasets:
        dataset_path = os.path.join(base_directory, dataset)
        images = get_image_paths_for_dataset(dataset_path)

        print(f"Total images before selection: {len(images)}")
        images = select_every_nth(images, n)
        print(f"Total images after selection: {len(images)}")

        json_path = os.path.join(dataset_path, 'images.json')
        with open(json_path, 'w') as f:
            json.dump(images, f)

    return "Created JSON files."


@app.route('/create_json')
def create_json():
    """Endpoint to trigger JSON creation."""
    n = int(request.args.get("n", 1))
    base_directory = os.path.join('static', 'data')
    message = create_json_files(base_directory, n)
    return message

@app.route('/')
def index():
    dataset = request.args.get("dataset")
    drag_sensitivity = float(request.args.get("dragSensitivity", 0.2))
    start_index = int(request.args.get("startIndex", 0))

    json_path = os.path.join("static", "data", dataset, "images.json")
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