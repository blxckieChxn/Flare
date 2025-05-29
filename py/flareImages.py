from io import BytesIO
from flask import Flask, request, jsonify, send_file
import mysql
from PIL import Image
import pytesseract
import sys

app = Flask(__name__)

def obtener_imagen_desde_bd(abreviatura):
    try:
        # Establecer conexión con la base de datos
        conn = mysql.connector.connect(
            host="localhost",
            user="flare",
            password="sdfsdf",
            database="flare"
        )

        if conn.is_connected():
            cursor = conn.cursor()

            # Ejecutar la consulta para obtener la ruta de la imagen
            consulta = "SELECT c.imagen FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)" \
            "WHERE e.abreviatura=%s ORDER BY num_carta;"
            cursor.execute(consulta,(abreviatura,))
            imagenes = cursor.fetchall()
            imagenes = [imagen[0] for imagen in imagenes]
            # Cerrar la conexión
            cursor.close()
            conn.close()

            return imagenes

    except mysql.connector.Error as e:
        print("Error al conectar a la base de datos:", e)
        return None

@app.route('/api/edicion', methods=['POST'])
def recibir_datos():
    data = request.get_json()
    abreviatura = data.get('abreviatura')
    print(f"Edicion recibida: {abreviatura}")
    return jsonify({"mensaje": "Datos recibidos correctamente."})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)



# Ruta URL que crea python para servir las img por el buscador web
@app.route('/img/<int:i>', methods=['POST'])
def mostrar_imagen():
    data = request.get_json()
    abreviatura = data.get("imagen")
    #Obtiene el path de la imagen en el sistema
    imagenes = obtener_imagen_desde_bd(abreviatura)
    print("[+] Imagen obtenida")
    
    # Suponiendo que siempre quieres la primera imagen (índice 0)
    if imagenes:
        return jsonify({"ruta": imagenes[0]})
    else:
        return jsonify({"error": "No se encontró imagen"}), 404
    

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=80)
