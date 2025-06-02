import hashlib
import logging
import mysql
import mysql.connector
from telegram import InlineKeyboardButton, InlineKeyboardMarkup, Update
from telegram.ext import Application, CommandHandler, MessageHandler, filters, CallbackContext, TypeHandler, JobQueue, Updater, CallbackQueryHandler
import time
from telegram import Bot
from telegram.constants import ParseMode
import asyncio
import aiomysql
from asyncio import Queue
from telegram import Update
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes

# Token
BOT_TOKEN = 'tu_token'

# Pre-assign menu text
WELCOME_MENU = "<b>Hola, soy Flare Bot</b>\n\n¿Que necesitas?"
INFO_MENU = "<b>BOT de la web Flare</b>\n\n Estoy para ayudarte con tus ventas =)"
LOGIN_MENU = "<b>Identificate</b>\n\n"
ID_MENU = "<b>Introduce tu ID de Flare</b>\n\n"
PEDIDOS_MENU = "<b>Bienvenido a Flare BOT</b>\n\n ¿Que pedidos necesitas consultar?"
WAITING_FOR_ID = "<b>[+] Esperando un ID de Flare...</b>"


# Botones de respuesta predefinidos
INFO_BUTTON = "Info"
LOGIN_BUTTON = "Login"
BACK_BUTTON = "<< Back"
ID_USER_BUTTON = "Identificarme con Flare"
SALES_BUTTON = "Mis pedidos"
P_BUTTON = "Pendientes"
L_BUTTON = "Listos"
E_BUTTON = "Enviados"

# Construir keyboard
WELCOME_MENU_MARKUP = [
    [InlineKeyboardButton(INFO_BUTTON, callback_data=INFO_BUTTON)],
    [InlineKeyboardButton(LOGIN_BUTTON, callback_data=LOGIN_BUTTON)],
]

LOGIN_MENU_MARKUP = [
    [InlineKeyboardButton(ID_USER_BUTTON, callback_data = ID_USER_BUTTON)],
    [InlineKeyboardButton(BACK_BUTTON, callback_data = BACK_BUTTON)],
]

SALES_MENU_MARKUP = [
    [InlineKeyboardButton(P_BUTTON, callback_data=P_BUTTON)],
    [InlineKeyboardButton(L_BUTTON, callback_data=L_BUTTON)],
    [InlineKeyboardButton(E_BUTTON, callback_data=E_BUTTON)],
    [InlineKeyboardButton(BACK_BUTTON, callback_data = BACK_BUTTON)],
]

logger = logging.getLogger(__name__)

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host='localhost',
    user='flare',
    password='sdfsdf',
    database='flare'
)
cursor = conexion.cursor()

# Configura tu token y chat_id
# Cambiar para mostrar en documentacion
TOKEN = 'tu_token'
CHAT_ID = 'tu_chat_id'

# Función para enviar mensajes de Telegram
async def send_telegram_message(message):
    bot = Bot(token=TOKEN)
    await bot.send_message(chat_id=CHAT_ID, text=message, parse_mode='HTML')
    
async def updateEstado(codPedido):
    # Conectar a la base de datos
    conn = await aiomysql.connect(
        host='localhost',
        port=3306,  # Puerto SQL, aparece en Xampp
        user='flare',
        password='flare',
        db='flare'
    )
    async with conn.cursor() as cursor:
        # Usar consulta parametrizada para evitar inyección SQL
        # Añadir a la documentacion en apartado de seguridad (margen de mejora)
        query = "UPDATE ventas SET estado = %s WHERE idVenta = %s;"
        await cursor.execute(query, ('l', codPedido))
        
        # Confirmar la transacción ¿No deberia ser try/catch?
        await conn.commit()
    
async def button_handler(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:

    """
    This handler processes the inline buttons on the menu
    """

    query = update.callback_query
    await query.answer()

    if query.data == INFO_BUTTON:
        await query.edit_message_text(INFO_MENU, parse_mode='HTML')

    elif query.data == LOGIN_BUTTON:
        # Segundo menú con dos botones
        submenu_keyboard = LOGIN_MENU_MARKUP
        reply_markup = InlineKeyboardMarkup(submenu_keyboard)
        await query.edit_message_text(LOGIN_MENU, reply_markup=reply_markup, parse_mode='HTML')

    elif query.data == ID_USER_BUTTON:
        await query.edit_message_text("🔐 <b>Introduce tu ID de Flare:</b>", parse_mode='HTML')
        context.user_data['login_step'] = 'awaiting_id'

    elif query.data in [P_BUTTON, L_BUTTON, E_BUTTON]:
        estados = {
            P_BUTTON: 'P',
            L_BUTTON: 'L',
            E_BUTTON: 'E'
            }

        estado = estados[query.data]

        query_text = """SELECT v.idVenta, v.comprador, p.idCarta, v.direccion, p.cantidad, v.emailComprador
                    FROM ventas v JOIN puesta_en p ON (v.idVenta = p.idVenta)
                    WHERE estado = %s
        """
        cursor.execute(query_text, (estado,))
        resultados = cursor.fetchall()

        if resultados:
            for fila in resultados:
            
                idVenta, uid, cartaId, direccion, cantidad, email = fila

                mensaje = (
                    f"-------------PEDIDO-------------\n"
                    f"<b>Pedido:</b> {idVenta}\n"
                    f"<b>Cliente:</b> {uid}\n"
                    f"<b>Carta:</b> {cartaId}\n"
                    f"<b>Dirección:</b> {direccion}\n"
                    f"<b>Cantidad:</b> {cantidad}\n"
                    f"<b>Email:</b> {email}\n"
                    f"---------------------------------"
                )

                await query.message.reply_text(mensaje, parse_mode='HTML')
        else:
            await query.message.reply_text("No hay pedidos con este estado.", parse_mode='HTML')

        await query.answer()

    elif query.data == BACK_BUTTON:
        await show_main_menu(update, context)

# Función que se ejecuta cuando el usuario usa el comando /start
# /start llama al menú principal
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await show_main_menu(update, context)
    
async def show_main_menu(update: Update, context: ContextTypes.DEFAULT_TYPE):
    keyboard = [
        [InlineKeyboardButton("Info", callback_data=INFO_BUTTON)],
        [InlineKeyboardButton("Login", callback_data=LOGIN_BUTTON)],
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)

    if update.callback_query:
        await update.callback_query.edit_message_text(
            WELCOME_MENU,
            reply_markup=reply_markup,
            parse_mode='HTML'
        )
    else:
        await update.message.reply_text(
            WELCOME_MENU,
            reply_markup=reply_markup,
            parse_mode='HTML'
        )

async def login_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    step = context.user_data.get('login_step')
    text = update.message.text.strip()

    # Paso 1: el usuario introduce su ID
    if step == 'awaiting_id':
        cursor.execute("SELECT password FROM usuarios WHERE uid = %s", (text,))
        result = cursor.fetchone()

        if result:
            context.user_data['login_step'] = 'awaiting_password'
            context.user_data['user_id'] = text
            context.user_data['expected_password'] = result[0]  # hash sha256 de la contraseña
            await update.message.reply_text("🔑 Introduce tu contraseña:")
        else:
            await update.message.reply_text("❌ ID no encontrado. Inténtalo de nuevo.\n\n🔐 Introduce tu ID de Flare:")
            context.user_data['login_step'] = 'awaiting_id'

    # Paso 2: el usuario introduce la contraseña
    elif step == 'awaiting_password':
        expected_hash = context.user_data.get('expected_password')
        entered_hash = hashlib.sha256(text.encode()).hexdigest()

        if entered_hash == expected_hash:
            await update.message.reply_text("✅ Identificación correcta. Accediendo al menú...")
            context.user_data.clear()

            # Aquí muestras el siguiente menú, por ejemplo el de pedidos
            await update.message.reply_text(
                "📦 ¿Qué pedidos deseas consultar?",
                reply_markup=InlineKeyboardMarkup(SALES_MENU_MARKUP),
                parse_mode='HTML'
            )
        else:
            await update.message.reply_text("❌ Contraseña incorrecta. Inténtalo de nuevo.")
            context.user_data['login_step'] = 'awaiting_password'

    # Si no está en ningún paso, ignoramos
    else:
        return

# Inicializa y corre el bot
if __name__ == '__main__':
    app = ApplicationBuilder().token(BOT_TOKEN).build()
    
    # Añadir comando /start
    app.add_handler(CommandHandler("start", start))
    # Handler de botones
    app.add_handler(CallbackQueryHandler(button_handler))
    # Login handler
    app.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, login_handler))


    # Iniciar el bot
    print("Bot funcionando...")
    app.run_polling()
