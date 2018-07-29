#include <SPI.h>
#include <MFRC522.h>
#include <KnxTpUart.h>
#include "keys.h"

#define SS_PIN 10
#define RST_PIN A1
#define SYNC_GA "0/7/255"       //Shared by all, disables all other readers if tag detected
#define WRITE_OK_GA "0/7/254"   //Shared by all, signales succesful id write
#define NEWID_GA "0/7/253"      //Shared by all, new id to place on tag
#define READTAG_GA "0/7/2"      //Must be unique for every different acces controll
#define PHYSICAL_ADDR "15.15.2"

void (* volatile stateFunc)() = &readNewTag;
uint16_t stateChangeMillis = 0;
char newId[16];

MFRC522 mfrc522(SS_PIN, RST_PIN);
KnxTpUart knx(&Serial, PHYSICAL_ADDR);

void setup()
{
  SPI.begin();
  mfrc522.PCD_Init();
  //Serial.begin(9600);
  
  Serial.begin(19200, SERIAL_8E1);
  knx.uartReset();
  knx.addListenGroupAddress(SYNC_GA);
  knx.addListenGroupAddress(NEWID_GA);
}

void loop()
{
  stateFunc();
}

void readNewTag()
{
  if ( !mfrc522.PICC_IsNewCardPresent()) return;
  if ( !mfrc522.PICC_ReadCardSerial()) return;

  if(mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, 7, &key, &mfrc522.uid) != MFRC522::STATUS_OK) {
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    delay(1000);
    return;
  }
  knx.groupWriteBool(SYNC_GA, true);
  //Serial.println("Tag detected, other readers disabled");
  delay(5);
  stateFunc = &readSyncronized;
}

void readSyncronized()
{
  char buffer[18];
  byte size = sizeof(buffer);
  if(mfrc522.MIFARE_Read(6, buffer, &size) == MFRC522::STATUS_OK) {
    buffer[16] = '\0';
    knx.groupWrite14ByteText(READTAG_GA, buffer);
    //Serial.print("Tag read: ");
    //Serial.println(buffer);
    stateChangeMillis = millis();
    stateFunc = &waitForNewId;
  }
  else {
    knx.groupWriteBool(SYNC_GA, false);
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    stateFunc = &readNewTag;
  }
}

void waitForNewId()
{
  if(uint16_t(millis() - stateChangeMillis) >= 3000) {
    knx.groupWriteBool(SYNC_GA, false);
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    stateFunc = &readNewTag;
  }
}

void programNewId()
{
  if(mfrc522.MIFARE_Write(6, newId, 16) == MFRC522::STATUS_OK) {
    knx.groupWriteBool(WRITE_OK_GA, true);
    //Serial.print("Write erfolgreich: ");
    //Serial.println(newId);
  }
  else {
    knx.groupWriteBool(WRITE_OK_GA, false);
    //Serial.println("Write fehlgeschlagen");
  }
  delay(50);
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
  stateFunc = &readNewTag;
  knx.groupWriteBool(SYNC_GA, false);
}

void syncWait()
{
  if(uint16_t(millis() - stateChangeMillis) >= 5000) {
    stateFunc = &readNewTag;
  }
}

void serialEvent()
{
  KnxTpUartSerialEventType eType = knx.serialEvent();
  if (eType == KNX_TELEGRAM) {
    KnxTelegram* telegram = knx.getReceivedTelegram();
    String target =
      String(0 + telegram->getTargetMainGroup())   + "/" +
      String(0 + telegram->getTargetMiddleGroup()) + "/" +
      String(0 + telegram->getTargetSubGroup());

    if (telegram->getCommand() == KNX_COMMAND_WRITE) {
      if (target == NEWID_GA) {
        if(stateFunc == &waitForNewId) {
          memset(newId, 0, 16);
          String knxId = telegram->get14ByteValue();
          for(byte i = 0; i < knxId.length(); ++i) {
            newId[i] = knxId[i];
          }
          stateFunc = &programNewId;
        }
      }
      else if(target == SYNC_GA) {
        String source =
          String(telegram->getSourceArea()) + "." +
          String(telegram->getSourceLine()) + "." +
          String(telegram->getSourceMember());
        if(source != PHYSICAL_ADDR) {
          if(telegram->getBool()) {
            stateFunc = &syncWait;
            //Serial.println("Disabled by sync");
          }
          else if(stateFunc == syncWait) {
            stateFunc = &readNewTag;
            mfrc522.PICC_HaltA();
            mfrc522.PCD_StopCrypto1();
            //Serial.println("Going back to work");
          }
        }
      }
    }
  }
}

