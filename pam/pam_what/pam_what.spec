# $Revision: 1.3 $, $Date: 2001/09/12 09:12:51 $
Summary:	PAM Modules to postgres connection
Summary(fr):	Module PAM pour la connection à une base postgres
Name:		pam_what
Version:	0.1.2
Release:	2
License:	GPL or BSD
Group:		Base
Source0:	ftp://ftp.souillac.anakeen.com/pub/anakeen/%{name}-%{version}.tar.gz
Vendor:         Anakeen           
URL:		http://www.anakeen.com
#BuildRequires:	pam-devel
#Requires:	make
Requires:	pam >= 0.72
Requires:       libwhat >= 0.4.5
Provides:	pam_what.so
BuildRoot:	%{_tmppath}/%{name}-%{version}-root-%(id -u -n)


%description
This PAM module is used to authent user with the WHAT database.
Only authent & account modules are provided

%description -l fr
Ce module PAM permet l'authentification d'utilisateur via la base de données de WHAT
Seuls les services d'authenfication et de compte sont fournis


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
%attr(0755,root,root) /lib/security/pam_what.so




%changelog
* Fri Jul 06 2001 Eric Brison <eric.brison@anakeen.com>
- Build first RPM


$Log: pam_what.spec,v $
Revision 1.3  2001/09/12 09:12:51  eric
all syslog are writen for LOG_DEBUG

Revision 1.2  2001/08/21 12:58:55  eric
correction fuite memoire

Revision 1.1  2001/07/31 08:26:21  eric
first

