/*
 * Pin layout should be as follows:
 * Signal     Pin              Pin               Pin
 *            Arduino Uno      Arduino Mega      MFRC522 board
 * ------------------------------------------------------------
 * Reset      9                5                 RST
 * SPI SS     10               53                SDA
 * SPI MOSI   11               51                MOSI
 * SPI MISO   12               50                MISO
 * SPI SCK    13               52                SCK
 */
#include <SPI.h>
#include <MFRC522.h>
#include <KnxTpUart.h>
#include "keys.h"

#define SS_PIN 10
#define RST_PIN 9
long programmTime = 5000;

MFRC522 mfrc522(SS_PIN, RST_PIN);
KnxTpUart knx(&Serial, "15.15.19");
volatile bool programm = false;
byte newId[16];

void setup()
{
  SPI.begin();
  mfrc522.PCD_Init();
  
  Serial.begin(19200);
  UCSR0C = UCSR0C | B00100000; // Even Parity
  knx.uartReset();
  knx.addListenGroupAddress("0/7/1");
}

void loop()
{
  MFRC522::StatusCode result;

  if(programm) {
    if(!programmId()) {
      knx.groupWrite14ByteText("0/7/2", "Timeout");
    }
    programm = false;
  }
  
  if ( !mfrc522.PICC_IsNewCardPresent()) return;
  if ( !mfrc522.PICC_ReadCardSerial()) return;

  if(mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, 7, &key, &mfrc522.uid) != MFRC522::STATUS_OK) {
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    delay(1000);
    return;
  }

  byte buffer[18];
  byte size = sizeof(buffer);
  if(mfrc522.MIFARE_Read(6, buffer, &size) == MFRC522::STATUS_OK) {
    buffer[16] = '\0';
    knx.groupWrite14ByteText("0/7/0", (const char*)buffer);
  }

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

bool programmId()
{
  long startTime = millis();
  byte tryKey = 0;
  do {
    if ( !mfrc522.PICC_IsNewCardPresent()) continue;
    if ( !mfrc522.PICC_ReadCardSerial()) continue;

    if(tryKey == 0) {
      if(mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, 7, &key, &mfrc522.uid) != MFRC522::STATUS_OK) {
        tryKey = 1;
        continue;
      }
    }
    else {
      if(mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, 7, &keyDefault, &mfrc522.uid) != MFRC522::STATUS_OK) {
        tryKey = 0;
        continue;
      }
      else {  //Unprogrammed Card
        byte buffer[18];
        byte size = sizeof(buffer);
        if(mfrc522.MIFARE_Read(7, buffer, &size) == MFRC522::STATUS_OK) {
          memcpy(buffer, key.keyByte, 6);
          if(mfrc522.MIFARE_Write(7, buffer, 16) == MFRC522::STATUS_OK) {
            knx.groupWrite14ByteText("0/7/2", "Neuer Tag");
            /*Serial.println("Fresh Card, programmed Key_A");
            printArray(buffer, 16);*/
          }
        }
      }
    }
    //Authenticated Card
    if(mfrc522.MIFARE_Write(6, newId, 16) == MFRC522::STATUS_OK) {
      knx.groupWrite14ByteText("0/7/2", "Program. OK");
      mfrc522.PICC_HaltA();
      mfrc522.PCD_StopCrypto1();
      return true;
    }
  }
  while((millis() - startTime) < programmTime);
  return false;
}

void serialEvent()
{
  KnxTpUartSerialEventType eType = knx.serialEvent();
  if (eType == TPUART_RESET_INDICATION) {
    
  }
  else if (eType == KNX_TELEGRAM) {
    KnxTelegram* telegram = knx.getReceivedTelegram();
    String target =
      String(0 + telegram->getTargetMainGroup())   + "/" +
      String(0 + telegram->getTargetMiddleGroup()) + "/" +
      String(0 + telegram->getTargetSubGroup());

    if (telegram->getCommand() == KNX_COMMAND_WRITE) {
      if (target == "0/7/1") {
        memset(newId, 0, 16);
        String knxId = telegram->get14ByteValue();
        for(byte i = 0; i < knxId.length(); ++i) {
          newId[i] = knxId[i];
        }
        programm = true;
      }
    }
  }
}

