# $Revision: 1.6 $, $Date: 2002/02/26 09:43:28 $
Summary:	PAM Modules to postgres connection
Summary(fr):	Module PAM pour la connection à une base postgres
Name:		pam_what_access
Version:	0.3.0
Release:	1
License:	GPL or BSD
Group:		Base
Source0:	ftp://ftp.souillac.anakeen.com/pub/anakeen/%{name}-%{version}.tar.gz
Vendor:         Anakeen           
URL:		http://www.anakeen.com
#BuildRequires:	pam-devel
#Requires:	make
Requires:	pam >= 0.72
Requires:       WHAT
BuildRoot:	%{_tmppath}/%{name}-%{version}-root-%(id -u -n)


%description
This PAM module is used to verify user accessibility with the WHAT database.
Only authent service is provided

%description -l fr
Ce module PAM permet de vérifier les droits utilisateur via la base de données de WHAT
Seul le service d'authentification est fourni

%prep
%setup -q -n %{name}-%{version}


%build

%configure \
	--with-postgres --bindir="/lib/security"
%{__make}

%install
rm -rf $RPM_BUILD_ROOT
install -d $RPM_BUILD_ROOT/lib/security

%{__make} install DESTDIR=$RPM_BUILD_ROOT


%post   
%postun 

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(644,root,root,755)
%attr(0755,root,root) /lib/security/pam_what_access.so




%changelog
* Fri Jul 06 2001 Eric Brison <eric.brison@anakeen.com>
- Build first RPM


$Log: pam_what_access.spec,v $
Revision 1.6  2002/02/26 09:43:28  yannick
Passage en Postgresql 7.2

Revision 1.5  2002/01/09 08:56:24  eric
change to new package WHAT

Revision 1.4  2001/09/12 09:18:40  eric
modif algo pour privilege groupes : compatible libwhat 0.4.8

Revision 1.3  2001/08/21 13:24:57  eric
modification pour nouvelle gestion des ACL

Revision 1.2  2001/08/21 13:21:30  eric
modification pour nouvelle gestion des ACL

Revision 1.1  2001/07/31 08:26:56  eric
first

