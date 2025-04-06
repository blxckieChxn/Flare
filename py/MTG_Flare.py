import logging
import mysql
import mysql.connector
from telegram import Update
from telegram.ext import Application, CommandHandler, MessageHandler, filters, CallbackContext, TypeHandler, JobQueue, Updater
import time
from telegram import Bot
from telegram.constants import ParseMode
import asyncio
import aiomysql


# Definir la clase pedido
class pedido:
    def __init__(self, idPedido, uid, idCarta, direccion, cantidad, email):
        self.idPedido=idPedido
        self.uid=uid
        self.idCarta=idCarta
        self.direccion=direccion
        self.cantidad=cantidad
        self.email=email

    def __str__(self):
        return (f"NUEVO PEDIDO {self.idPedido}\n=====================\n\n Cliente {self.uid}: {self.direccion}\nCarta: {self.idCarta}, cantidad: {self.cantidad}\nCorreo de contacto: {self.email}")


logger = logging.getLogger(__name__)

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='mtg'
)
cursor = conexion.cursor()

# Configura tu token y chat_id
TOKEN = '7417727967:AAHqassfE5Fk_aE4It7d6K8FT_4UyWyUwVI'
CHAT_ID = '1247409867'

# Función para enviar mensajes de Telegram
def send_telegram_message(message):
    bot = Bot(token=TOKEN)
    bot.send_message(chat_id=CHAT_ID, text=message)

# Función para verificar nuevos inserts
def check_new_inserts():
    cursor.execute("SELECT * FROM pedidos WHERE estado = 'pendiente'")
    row = cursor.fetchone()
    if row:
        return row
    return None

async def updateEstado(codPedido):
    # Conectar a la base de datos
    conn = await aiomysql.connect(
        host='localhost',
        port=3306,  # Puerto SQL, aparece en Xampp
        user='root',
        password='',
        db='mtg'
    )
    async with conn.cursor() as cursor:
        # Usar consulta parametrizada para evitar inyección SQL
        query = "UPDATE pedidos SET estado = %s WHERE idPedido = %s;"
        await cursor.execute(query, ('procesado', codPedido))
        
        # Confirmar la transacción
        await conn.commit()

# Función para enviar mensajes de Telegram
async def send_telegram_message(message):
    bot = Bot(token=TOKEN)
    await bot.send_message(chat_id=CHAT_ID, text=message, parse_mode=ParseMode.HTML)

async def main():
    print('Esperando nuevos inserts...')
    
    while True:
        row = check_new_inserts()
        if row:
            idPedido, uid, cartaId, direccionEnvio, cantidad, email, estado = row
            if estado == 'pendiente':
                message = (f"<b>Nueva fila insertada:</b>\n"
                            f"<b>idPedido:</b> {idPedido}\n"
                            f"<b>UID:</b> {uid}\n"
                            f"<b>Carta ID:</b> {cartaId}\n"
                            f"<b>Dirección de Envío:</b> {direccionEnvio}\n"
                            f"<b>Cantidad:</b> {cantidad}\n"
                            f"<b>Email:</b> {email}\n"
                            f"<b>Estado:</b> {estado}")
                await send_telegram_message(message)
                await updateEstado(idPedido)
        await asyncio.sleep(5)  # Espera 5 segundos antes de volver a verificar


# Ejecutar el bucle de eventos asyncio
if __name__ == '__main__':
    asyncio.run(main())
    