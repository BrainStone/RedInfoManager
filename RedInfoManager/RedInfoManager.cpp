/**
  * Das ist die Haupdatei hier wird alles gestartet!
  *
  * Author: BrainStone
  * Projekt-Version:
  * v0.0.2
*/

#include "stdafx.h"
#include "RedInfoManagerForm.h"

using namespace RedInfoManager;

[STAThreadAttribute]
int main(array<System::String ^> ^args)
{
	// Aktivieren visueller Effekte von Windows XP, bevor Steuerelemente erstellt werden
	Application::EnableVisualStyles();
	Application::SetCompatibleTextRenderingDefault(false); 

	// Hauptfenster erstellen und ausführen
	Application::Run(gcnew RedInfoManagerForm());
	return 0;
}
