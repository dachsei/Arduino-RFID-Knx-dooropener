#include <SPI.h>
#include <MFRC522.h>
#include "../keys.h"

#define SS_PIN 10
#define RST_PIN A1

byte key_block[16];

MFRC522 mfrc522(SS_PIN, RST_PIN);

void setup()
{
  memcpy(key_block, key.keyByte, 6);
  key_block[6] = 0b11111111;
  key_block[7] = 0b00000111;
  key_block[8] = 0b10000000;
  key_block[9] = 0x00;
  memset(key_block + 10, 0x00, 6);
  
  SPI.begin();
  mfrc522.PCD_Init();
  
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, LOW);
}

void loop()
{
  if (!mfrc522.PICC_IsNewCardPresent()) return;
  if (!mfrc522.PICC_ReadCardSerial()) return;

  if (mfrc522.PCD_Authenticate(MFRC522::PICC_CMD_MF_AUTH_KEY_A, 7, &keyDefault, &mfrc522.uid) != MFRC522::STATUS_OK) {
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    error();
    return;
  }

  if (mfrc522.MIFARE_Write(7, key_block, 16) == MFRC522::STATUS_OK) {
    digitalWrite(LED_BUILTIN, HIGH);
    delay(500);
    digitalWrite(LED_BUILTIN, LOW);
  }
  else {
    error();
  }
  
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

void error()
{
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
  digitalWrite(LED_BUILTIN, HIGH);
  delay(100);
  digitalWrite(LED_BUILTIN, LOW);
  delay(100);
}
