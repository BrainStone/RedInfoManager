/**
  * Das ist die Haupdatei hier wird alles gestartet!
  *
  * Author: BrainStone
  * Projekt-Version:
  * v0.0.0
*/

#include "stdafx.h"
#include "Form1.h"

using namespace RedInfoManager;

[STAThreadAttribute]
int main(array<System::String ^> ^args)
{
	// Aktivieren visueller Effekte von Windows XP, bevor Steuerelemente erstellt werden
	Application::EnableVisualStyles();
	Application::SetCompatibleTextRenderingDefault(false); 

	// Hauptfenster erstellen und ausführen
	Application::Run(gcnew Form1());
	return 0;
}
