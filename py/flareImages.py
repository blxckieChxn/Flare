from io import BytesIO
import re
from flask import Flask, request, jsonify
from PIL import Image
import pytesseract
import enchant

app = Flask(__name__)

def limpiarTexto(texto_raw):
    # Solo palabras de al menos 2 letras, sin números ni símbolos y que empiecen por mayuscula
    palabras = re.findall(r'\b[A-Z][a-zA-Z]{1,}\b', texto_raw)
    palabras = palabrasValidas(palabras)

    return palabras

def palabrasValidas(palabras):
    dic = enchant.Dict("en_US")
    return [p for p in palabras if dic.check(p)]

def extraerTexto(imagen):
    try:
        # Posiciona el puntero al inicio del archivo
        imagen.stream.seek(0)
        img = Image.open(imagen.stream)

        # Escalar la imagen para mejor entendimiento
        img = img.resize((img.width * 2, img.height * 2), Image.Resampling.LANCZOS)
        # Convertir a escala de grises para aumentar contraste entre letras y fondo
        img = img.convert('L')

        config = '--oem 3 --psm 6'
        texto = pytesseract.image_to_string(img, lang='eng', config=config)
        palabras = limpiarTexto(texto)

        return ' '.join(palabras)

    except Exception as e:
        return f'Error al extraer texto: {str(e)}'
    

@app.route('/ocr', methods=['POST'])
def ocr():
    if 'imagen' not in request.files:
        return jsonify({'error': 'No se recibió ninguna imagen'}), 400

    imagen = request.files['imagen']
    texto = extraerTexto(imagen)

    return jsonify({'texto': texto})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5001)
