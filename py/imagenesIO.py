from flask import Flask, send_file, request
from io import BytesIO
import mysql.connector

app = Flask(__name__)
i='0'

def obtener_edicion_busqueda():
    try:
        # Establecer conexión con la base de datos
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="mtg"
        )

        if conn.is_connected():
            cursor = conn.cursor()

            # Ejecutar la consulta para obtener la imagen
            cursor.execute("SELECT edicion FROM edicion_busqueda WHERE id=1;")
            edicion = cursor.fetchone()[0]
            # Cerrar la conexión
            cursor.close()
            conn.close()

            return edicion 
        
    except mysql.connector.Error as e:
        print("Error al conectar a la base de datos:", e)
        return None


def obtener_imagen_desde_bd(edicion):
    try:
        # Establecer conexión con la base de datos
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="mtg"
        )

        if conn.is_connected():
            cursor = conn.cursor()

            # Ejecutar la consulta para obtener la imagen
            consulta = "SELECT imagen FROM cartas WHERE edicion=%s ORDER BY num_carta;"
            cursor.execute(consulta,(edicion,))
            imagenes = cursor.fetchall()
            imagenes = [imagen[0] for imagen in imagenes]
            # Cerrar la conexión
            cursor.close()
            conn.close()

            return imagenes

    except mysql.connector.Error as e:
        print("Error al conectar a la base de datos:", e)
        return None

  
@app.route('/img/<int:i>')
def mostrar_imagen(i):
    edicion = obtener_edicion_busqueda()
    imagenes = obtener_imagen_desde_bd(edicion)

    # Obtener el valor de 'i' de los parámetros de la URL
    indice = int(request.args.get('i', 0))
    
    # Verificar si el índice está dentro del rango de nombres_imagenes
    if 0 <= indice < len(imagenes):
        imagenes = BytesIO(imagenes[i])
        return send_file(imagenes, mimetype='image/png')
    else:
        return 'Imagen no encontrada', 404
    

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=80)
       