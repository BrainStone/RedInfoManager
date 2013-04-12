#pragma once

namespace RedInfoManager {

	using namespace System;
	using namespace System::ComponentModel;
	using namespace System::Collections;
	using namespace System::Windows::Forms;
	using namespace System::Data;
	using namespace System::Drawing;

	/// <summary>
	/// Zusammenfassung für RedInfoManagerForm
	/// </summary>
	public ref class RedInfoManagerForm : public System::Windows::Forms::Form
	{
	public:
		RedInfoManagerForm(void)
		{
			InitializeComponent();
			//
			//TODO: Konstruktorcode hier hinzufügen.
			//
		}

	protected:
		/// <summary>
		/// Verwendete Ressourcen bereinigen.
		/// </summary>
		~RedInfoManagerForm()
		{
			if (components)
			{
				delete components;
			}
		}
	private:
	protected: 

	private:
		/// <summary>
		/// Erforderliche Designervariable.
		/// </summary>
		System::ComponentModel::Container ^components;

		void InitializeComponent(void)
		{
			this->SuspendLayout();
			// 
			// Form1
			// 
			this->AutoScaleDimensions = System::Drawing::SizeF(6, 13);
			this->AutoScaleMode = System::Windows::Forms::AutoScaleMode::Font;
			this->ClientSize = System::Drawing::Size(915, 523);
			this->Name = L"RedInfoManager";
			this->Text = L"RedInfoManager";
			this->ResumeLayout(false);

		}
	};
}